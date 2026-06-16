<?php

namespace App\Support;

class PaymentProviderCatalog
{
    public const PROVIDERS = [
        'stripe' => [
            'name' => 'Stripe',
            'environments' => ['sandbox', 'live'],
            'capabilities' => ['card', 'wallet', 'payment_intent', 'webhook'],
            'credential_fields' => [
                ['key' => 'publishable_key', 'type' => 'string', 'secret' => false, 'required' => true],
                ['key' => 'restricted_key', 'type' => 'string', 'secret' => true, 'required' => false],
                ['key' => 'secret_key', 'type' => 'string', 'secret' => true, 'required' => false],
                ['key' => 'webhook_signing_secret', 'type' => 'string', 'secret' => true, 'required' => false],
                ['key' => 'account_id', 'type' => 'string', 'secret' => false, 'required' => false],
            ],
            'required_any' => [['restricted_key', 'secret_key']],
            'public_fields' => ['publishable_key'],
        ],
        'paypal' => [
            'name' => 'PayPal',
            'environments' => ['sandbox', 'live'],
            'capabilities' => ['paypal_wallet', 'orders', 'capture', 'webhook'],
            'credential_fields' => [
                ['key' => 'client_id', 'type' => 'string', 'secret' => false, 'required' => true],
                ['key' => 'client_secret', 'type' => 'string', 'secret' => true, 'required' => true],
                ['key' => 'webhook_id', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'merchant_id', 'type' => 'string', 'secret' => false, 'required' => false],
            ],
            'required_any' => [],
            'public_fields' => ['client_id'],
        ],
        'bank_transfer' => [
            'name' => 'Bank transfer',
            'environments' => ['manual'],
            'capabilities' => ['offline', 'manual_validation'],
            'credential_fields' => [
                ['key' => 'account_holder', 'type' => 'string', 'secret' => false, 'required' => true],
                ['key' => 'iban', 'type' => 'string', 'secret' => false, 'required' => true],
                ['key' => 'bic', 'type' => 'string', 'secret' => false, 'required' => false],
                ['key' => 'bank_name', 'type' => 'string', 'secret' => false, 'required' => false],
            ],
            'required_any' => [],
            'public_fields' => ['account_holder', 'iban', 'bic', 'bank_name'],
        ],
        'cash_on_delivery' => [
            'name' => 'Cash on delivery',
            'environments' => ['manual'],
            'capabilities' => ['offline', 'manual_validation'],
            'credential_fields' => [],
            'required_any' => [],
            'public_fields' => [],
        ],
        'prestashop' => [
            'name' => 'PrestaShop connector',
            'environments' => ['sandbox', 'live'],
            'capabilities' => ['external_channel', 'order_sync', 'payment_status_sync'],
            'credential_fields' => [
                ['key' => 'shop_url', 'type' => 'url', 'secret' => false, 'required' => true],
                ['key' => 'webservice_key', 'type' => 'string', 'secret' => true, 'required' => true],
                ['key' => 'module_secret', 'type' => 'string', 'secret' => true, 'required' => false],
            ],
            'required_any' => [],
            'public_fields' => ['shop_url'],
        ],
        'tiktok_shop' => [
            'name' => 'TikTok Shop connector',
            'environments' => ['sandbox', 'live'],
            'capabilities' => ['external_channel', 'order_sync', 'payment_status_sync'],
            'credential_fields' => [
                ['key' => 'app_key', 'type' => 'string', 'secret' => false, 'required' => true],
                ['key' => 'app_secret', 'type' => 'string', 'secret' => true, 'required' => true],
                ['key' => 'shop_cipher', 'type' => 'string', 'secret' => true, 'required' => false],
                ['key' => 'access_token', 'type' => 'string', 'secret' => true, 'required' => false],
                ['key' => 'refresh_token', 'type' => 'string', 'secret' => true, 'required' => false],
            ],
            'required_any' => [],
            'public_fields' => ['app_key'],
        ],
    ];

    public const STATUSES = ['draft', 'active', 'inactive'];

    public static function providers(): array
    {
        return self::PROVIDERS;
    }

    public static function provider(string $provider): ?array
    {
        return self::PROVIDERS[$provider] ?? null;
    }

    public static function providerKeys(): array
    {
        return array_keys(self::PROVIDERS);
    }
}
