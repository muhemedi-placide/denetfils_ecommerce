<?php

namespace App\Services\Shipping\MondialRelay;

use RuntimeException;

class MondialRelayRejectedException extends RuntimeException
{
    public function __construct(
        public readonly string $operation,
        public readonly string $status,
        public readonly array $payload = [],
        ?string $message = null,
    ) {
        parent::__construct($message ?: "Mondial Relay rejected {$operation} (status {$status}).");
    }
}
