<?php

namespace App\Services\Shipping\MondialRelay;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

class MondialRelayClient
{
    public function __construct(private MondialRelaySignature $signature) {}

    public function call(string $operation, array $parameters, array $credentials): array
    {
        $enseigne = trim((string) ($credentials['enseigne'] ?? ''));
        $privateKey = trim((string) ($credentials['private_key'] ?? ''));
        if ($enseigne === '' || $privateKey === '') {
            throw new RuntimeException('Mondial Relay configuration is incomplete.');
        }

        $parameters = ['Enseigne' => $enseigne, ...$parameters];
        $parameters['Security'] = $this->signature->make(array_values($parameters), $privateKey);
        $endpoint = rtrim((string) ($credentials['api_endpoint'] ?? config('shipping.mondial_relay.endpoint')), '/');

        try {
            $response = $this->request()
                ->withHeaders(['SOAPAction' => 'http://www.mondialrelay.fr/webservice/'.$operation])
                ->withBody($this->envelope($operation, $parameters), 'text/xml; charset=utf-8')
                ->post($endpoint);
        } catch (ConnectionException $exception) {
            $host = parse_url($endpoint, PHP_URL_HOST) ?: $endpoint;
            throw new RuntimeException("Mondial Relay is unreachable from this server. DNS/network resolution failed for {$host}. Check internet access, DNS, firewall or proxy settings.", previous: $exception);
        }
        if (! $response->successful()) {
            throw new RuntimeException("Mondial Relay HTTP error {$response->status()}.");
        }

        return $this->decode($response->body(), $operation);
    }

    public function download(string $url): string
    {
        $response = $this->request()->get($url);
        if (! $response->successful() || $response->body() === '') {
            throw new RuntimeException('Mondial Relay label download failed.');
        }
        return $response->body();
    }

    private function request(): PendingRequest
    {
        return Http::accept('application/xml')->timeout((int) config('shipping.mondial_relay.timeout', 15))->retry(2, 250, throw: false);
    }

    private function envelope(string $operation, array $parameters): string
    {
        $elements = collect($parameters)
            ->map(fn (mixed $value, string $name) => sprintf(
                '<%1$s>%2$s</%1$s>',
                htmlspecialchars($name, ENT_XML1 | ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8'),
            ))
            ->implode('');

        return '<?xml version="1.0" encoding="utf-8"?>'
            .'<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
            .'<soap:Body><'.$operation.' xmlns="http://www.mondialrelay.fr/webservice/">'.$elements.'</'.$operation.'></soap:Body></soap:Envelope>';
    }

    private function decode(string $xml, string $operation): array
    {
        $previous = libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if ($document === false) {
            throw new RuntimeException('Mondial Relay returned invalid XML.');
        }

        if (strcasecmp($document->getName(), 'html') === 0) {
            throw new RuntimeException('Mondial Relay returned an HTML error page.');
        }

        $faults = $document->xpath('//*[local-name()="Fault"]');
        if ($faults !== false && isset($faults[0])) {
            $faultStrings = $faults[0]->xpath('.//*[local-name()="faultstring"]');
            throw new RuntimeException('Mondial Relay SOAP fault: '.trim((string) ($faultStrings[0] ?? 'unknown fault')));
        }

        $results = $document->xpath('//*[local-name()="'.$operation.'Result"]');
        if ($results !== false && isset($results[0])) {
            $document = $results[0];
        }

        $decoded = json_decode(json_encode($document, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        if (is_array($decoded)) {
            return $decoded;
        }

        $value = trim((string) $document);
        return $value === '' ? [] : ['value' => $value];
    }
}
