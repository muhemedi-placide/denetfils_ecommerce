<?php

namespace App\Services\Shipping\MondialRelay;

final class MondialRelaySignature
{
    public function make(array $orderedValues, string $privateKey): string
    {
        return strtoupper(md5(implode('', array_map(static fn (mixed $value) => (string) ($value ?? ''), $orderedValues)).$privateKey));
    }
}
