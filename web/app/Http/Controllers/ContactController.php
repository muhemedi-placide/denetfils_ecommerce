<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function __invoke(string $locale): View
    {
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : config('app.locale', 'fr');

        app()->setLocale($locale);

        return view('pages.contact', [
            'locale' => $locale,
            'activeMenu' => 'contact',
        ]);
    }
}
