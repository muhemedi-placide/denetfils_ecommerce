<?php

namespace Tests\Unit\Shipping;

use App\Services\Shipping\MondialRelay\MondialRelaySignature;
use PHPUnit\Framework\TestCase;

class MondialRelaySignatureTest extends TestCase
{
    public function test_signature_preserves_parameter_order_and_is_uppercase_md5(): void
    {
        $signature = (new MondialRelaySignature)->make(['BDTEST', 'FR', '75001', 1000], 'PRIVATE');
        $this->assertSame(strtoupper(md5('BDTESTFR750011000PRIVATE')), $signature);
    }
}
