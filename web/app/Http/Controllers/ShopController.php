<?php

namespace App\Http\Controllers;

use App\Services\ShopApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function home(Request $request, ShopApiClient $api, ?string $locale = null): View
    {
        $locale = $this->setLocale($locale);
        $filters = $this->filters($request);
        $categories = $api->categories($locale);
        $products = $api->products($locale, $filters);

        return view('welcome', [
            'locale' => $locale,
            'categories' => $categories['data'],
            'products' => $products['data'],
            'apiError' => $products['error'],
            'filters' => $filters,
        ]);
    }

    public function show(ShopApiClient $api, string $locale, string $slug): View
    {
        $locale = $this->setLocale($locale);
        $product = $api->product($slug, $locale);

        abort_if(! $product, 404);

        return view('products.show', [
            'locale' => $locale,
            'product' => $product,
        ]);
    }

    private function setLocale(?string $locale): string
    {
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : config('app.locale', 'fr');

        app()->setLocale($locale);

        return $locale;
    }

    private function filters(Request $request): array
    {
        $sort = $request->query('sort', 'default');

        return [
            'category' => (string) $request->query('category', ''),
            'q' => trim((string) $request->query('q', '')),
            'sort' => in_array($sort, ['default', 'price_asc', 'price_desc', 'latest'], true) ? $sort : 'default',
        ];
    }
}
