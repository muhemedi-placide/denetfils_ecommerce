<?php

namespace App\Support;

class OrderStatusCatalog
{
    public const ORDER_STATUSES = [
        'pending_payment',
        'confirmed',
        'processing',
        'completed',
        'cancelled',
        'refunded',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid',
        'authorized',
        'paid',
        'failed',
        'partially_refunded',
        'refunded',
    ];

    public const FULFILLMENT_STATUSES = [
        'unfulfilled',
        'preparing',
        'ready_to_ship',
        'shipped',
        'delivered',
        'returned',
        'cancelled',
    ];

    private const LABELS = [
        'fr' => [
            'order' => [
                'pending_payment' => 'Paiement en attente',
                'confirmed' => 'Confirmee',
                'processing' => 'En traitement',
                'completed' => 'Terminee',
                'cancelled' => 'Annulee',
                'refunded' => 'Remboursee',
            ],
            'payment' => [
                'unpaid' => 'Non payee',
                'authorized' => 'Autorisee',
                'paid' => 'Payee',
                'failed' => 'Echec paiement',
                'partially_refunded' => 'Remboursement partiel',
                'refunded' => 'Remboursee',
            ],
            'fulfillment' => [
                'unfulfilled' => 'Non preparee',
                'preparing' => 'En preparation',
                'ready_to_ship' => 'Prete a expedier',
                'shipped' => 'Expediee',
                'delivered' => 'Livree',
                'returned' => 'Retournee',
                'cancelled' => 'Annulee',
            ],
        ],
        'en' => [
            'order' => [
                'pending_payment' => 'Pending payment',
                'confirmed' => 'Confirmed',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
            ],
            'payment' => [
                'unpaid' => 'Unpaid',
                'authorized' => 'Authorized',
                'paid' => 'Paid',
                'failed' => 'Payment failed',
                'partially_refunded' => 'Partially refunded',
                'refunded' => 'Refunded',
            ],
            'fulfillment' => [
                'unfulfilled' => 'Unfulfilled',
                'preparing' => 'Preparing',
                'ready_to_ship' => 'Ready to ship',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'returned' => 'Returned',
                'cancelled' => 'Cancelled',
            ],
        ],
    ];

    public static function label(string $group, ?string $status, string $locale = 'fr'): string
    {
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';

        return self::LABELS[$locale][$group][$status] ?? (string) $status;
    }

    public static function options(string $group, string $locale = 'fr'): array
    {
        $statuses = match ($group) {
            'order' => self::ORDER_STATUSES,
            'payment' => self::PAYMENT_STATUSES,
            'fulfillment' => self::FULFILLMENT_STATUSES,
            default => [],
        };

        return collect($statuses)
            ->map(fn (string $status) => [
                'value' => $status,
                'label' => self::label($group, $status, $locale),
            ])
            ->values()
            ->all();
    }
}
