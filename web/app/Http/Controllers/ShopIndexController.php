<?php

namespace App\Http\Controllers;

use App\Services\ShopApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShopIndexController extends Controller
{
    public function __invoke(Request $request, ShopApiClient $api, string $locale): View
    {
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : config('app.locale', 'fr');
        app()->setLocale($locale);

        $sort = $request->query('sort', 'default');
        $filters = [
            'category' => (string) $request->query('category', ''),
            'q' => trim((string) $request->query('q', '')),
            'sort' => in_array($sort, ['default', 'price_asc', 'price_desc', 'latest'], true) ? $sort : 'default',
        ];

        $categories = $api->categories($locale);
        $products = $api->products($locale, $filters);

        return view('pages.shop', [
            'locale' => $locale,
            'categories' => $categories['data'],
            'products' => $products['data'],
            'apiError' => $products['error'],
            'filters' => $filters,
            'activeMenu' => 'products',
        ]);
    }
}
