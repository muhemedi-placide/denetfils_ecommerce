<?php

namespace App\Support;

class MoneyFormatter
{
    public static function format(int $cents, string $currency = 'EUR', string $locale = 'fr'): string
    {
        $amount = number_format($cents / 100, 2, $locale === 'fr' ? ',' : '.', $locale === 'fr' ? ' ' : ',');

        return $currency === 'EUR'
            ? ($locale === 'fr' ? "{$amount} EUR" : "EUR {$amount}")
            : "{$amount} {$currency}";
    }
}
