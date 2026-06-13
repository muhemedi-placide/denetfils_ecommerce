<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportedCountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = in_array($request->query('locale'), ['fr', 'en'], true) ? $request->query('locale') : 'fr';

        return [
            'code' => $this->code,
            'name' => $this->localized('name', $locale),
            'currency' => $this->currency,
            'default_locale' => $this->default_locale,
            'timezone' => $this->timezone,
            'standard_vat_rate_percent' => $this->standard_vat_rate_percent,
            'food_vat_rate_percent' => $this->food_vat_rate_percent,
            'is_eu' => $this->is_eu,
            'is_active' => $this->is_active,
        ];
    }
}
