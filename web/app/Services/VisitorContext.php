<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VisitorContext
{
    public function resolve(Request $request): array
    {
        [$country, $countrySource] = $this->country($request);
        [$locale, $localeSource] = $this->locale($request, $country);
        $supportedCountries = config('localization.supported_countries', ['FR']);

        return [
            'country_code' => $country,
            'country_source' => $countrySource,
            'locale' => $locale,
            'locale_source' => $localeSource,
            'is_supported' => in_array($country, $supportedCountries, true),
            'is_estimate' => ! in_array($countrySource, ['account', 'manual'], true),
            'supported_countries' => $this->countryOptions($locale),
        ];
    }

    private function country(Request $request): array
    {
        $manual = $this->validCountry($request->cookie('visitor_country'));
        if ($manual) {
            return [$manual, 'manual'];
        }

        $address = $this->validCountry($request->session()->get('customer_shipping_country'));
        if ($address) {
            return [$address, 'address'];
        }

        $account = $this->validCountry(data_get($request->session()->get('customer_user'), 'country_code'));
        if ($account) {
            return [$account, 'account'];
        }

        if (config('localization.cloudflare.trust_country_header')) {
            $cloudflare = $this->validCountry($request->header('CF-IPCountry'));
            if ($cloudflare && ! in_array($cloudflare, ['XX', 'T1'], true)) {
                return [$cloudflare, 'cloudflare'];
            }
        }

        $ipinfo = $this->ipinfoCountry($request->ip());
        if ($ipinfo) {
            return [$ipinfo, 'ipinfo'];
        }

        return [strtoupper(config('localization.default_country', 'FR')), 'default'];
    }

    private function locale(Request $request, string $country): array
    {
        $routeLocale = $this->validLocale($request->route('locale'));
        if ($routeLocale) {
            return [$routeLocale, 'url'];
        }

        $manual = $this->validLocale($request->cookie('visitor_locale'));
        if ($manual) {
            return [$manual, 'manual'];
        }

        $account = $this->validLocale(data_get($request->session()->get('customer_user'), 'preferred_locale'));
        if ($account) {
            return [$account, 'account'];
        }

        if ($request->hasHeader('Accept-Language')) {
            $browser = $this->validLocale($request->getPreferredLanguage(config('localization.supported_locales', ['fr', 'en'])));
            if ($browser) {
                return [$browser, 'browser'];
            }
        }

        $countryLocale = $this->validLocale(config("localization.country_locales.{$country}"));
        if ($countryLocale) {
            return [$countryLocale, 'country'];
        }

        return [config('localization.default_locale', 'fr'), 'default'];
    }

    private function ipinfoCountry(?string $ip): ?string
    {
        $token = (string) config('localization.ipinfo.token');
        if ($token === '' || ! filter_var($ip, FILTER_VALIDATE_IP) || $this->isPrivateIp($ip)) {
            return null;
        }

        $key = 'visitor-country:'.hash('sha256', $ip);

        $country = Cache::remember($key, config('localization.ipinfo.cache_seconds', 86400), function () use ($ip, $token) {
            try {
                $response = Http::acceptJson()
                    ->withToken($token)
                    ->timeout(config('localization.ipinfo.timeout_seconds', 1.5))
                    ->get(rtrim(config('localization.ipinfo.base_url'), '/')."/{$ip}");

                return $response->successful()
                    ? ($this->validCountry($response->json('country_code')) ?? '__none__')
                    : '__none__';
            } catch (ConnectionException) {
                return '__none__';
            }
        });

        return $country === '__none__' ? null : $this->validCountry($country);
    }

    private function countryOptions(string $locale): array
    {
        return collect(config('localization.supported_countries', ['FR']))
            ->map(fn (string $code) => [
                'code' => $code,
                'name' => config("localization.country_names.{$code}.{$locale}", $code),
            ])
            ->values()
            ->all();
    }

    private function validCountry(mixed $country): ?string
    {
        $country = strtoupper(trim((string) $country));

        return preg_match('/^[A-Z]{2}$/', $country) ? $country : null;
    }

    private function validLocale(mixed $locale): ?string
    {
        $locale = strtolower(trim((string) $locale));

        return in_array($locale, config('localization.supported_locales', ['fr', 'en']), true) ? $locale : null;
    }

    private function isPrivateIp(string $ip): bool
    {
        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
