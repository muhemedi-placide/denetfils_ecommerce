<?php

namespace App\Support;

trait LocalizesJson
{
    public function localized(string $attribute, string $locale): ?string
    {
        $values = $this->getAttribute($attribute);

        if (! is_array($values)) {
            return $values;
        }

        return $values[$locale] ?? $values['fr'] ?? $values['en'] ?? reset($values) ?: null;
    }
}
