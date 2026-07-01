<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;

class VisitorPreferenceController extends Controller
{
    public function landing(Request $request): RedirectResponse
    {
        $locale = data_get($request->attributes->get('visitor_context'), 'locale', 'fr');

        return redirect()->route('home.localized', ['locale' => $locale]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'country_code' => ['nullable', 'string', Rule::in(config('localization.supported_countries'))],
            'locale' => ['nullable', Rule::in(config('localization.supported_locales'))],
            'return_to' => ['nullable', 'string', 'max:2048'],
        ]);

        if (! empty($data['country_code'])) {
            Cookie::queue('visitor_country', strtoupper($data['country_code']), config('localization.cookie_minutes'));
        }
        if (! empty($data['locale'])) {
            Cookie::queue('visitor_locale', $data['locale'], config('localization.cookie_minutes'));
        }

        $path = $this->safePath($data['return_to'] ?? '/');
        if (! empty($data['locale'])) {
            $path = preg_replace('#^/(fr|en)(?=/|$)#', '/'.$data['locale'], $path);
            if (! preg_match('#^/(fr|en)(?=/|$)#', $path)) {
                $path = '/'.$data['locale'];
            }
        }

        return redirect($path);
    }

    private function safePath(string $path): string
    {
        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '/';
        }

        return $path;
    }
}
