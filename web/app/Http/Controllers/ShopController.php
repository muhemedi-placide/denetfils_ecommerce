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
            'activeMenu' => 'home',
        ]);
    }

    public function about(string $locale): View
    {
        $locale = $this->setLocale($locale);

        return view('pages.about', [
            'locale' => $locale,
            'activeMenu' => 'about',
        ]);
    }

    public function blog(string $locale): View
    {
        $locale = $this->setLocale($locale);

        return view('blog.index', [
            'locale' => $locale,
            'activeMenu' => 'blog',
        ]);
    }

    public function delivery(string $locale): View
    {
        return $this->utilityPage($locale, 'delivery');
    }

    public function legalNotice(string $locale): View
    {
        return $this->utilityPage($locale, 'legal');
    }

    public function terms(string $locale): View
    {
        return $this->utilityPage($locale, 'terms');
    }

    public function securePayment(string $locale): View
    {
        return $this->utilityPage($locale, 'payment');
    }

    public function show(ShopApiClient $api, string $locale, string $slug): View
    {
        $locale = $this->setLocale($locale);
        $product = $api->product($slug, $locale);

        abort_if(! $product, 404);

        $relatedProducts = $this->relatedProducts($api, $locale, $product);

        return view('products.show', [
            'locale' => $locale,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'activeMenu' => 'products',
        ]);
    }

    private function utilityPage(string $locale, string $page): View
    {
        $locale = $this->setLocale($locale);

        abort_unless(array_key_exists($page, trans('home.utility_pages')), 404);

        return view('pages.utility', [
            'locale' => $locale,
            'page' => $page,
            'content' => trans('home.utility_pages.' . $page),
            'activeMenu' => $page === 'legal' || $page === 'terms' ? 'about' : 'home',
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

    private function relatedProducts(ShopApiClient $api, string $locale, array $product): array
    {
        $categorySlug = (string) data_get($product, 'category.slug', '');

        $response = $api->products($locale, [
            'category' => $categorySlug,
            'sort' => 'latest',
        ]);

        return collect($response['data'])
            ->reject(fn (array $item) => (int) ($item['id'] ?? 0) === (int) ($product['id'] ?? 0))
            ->take(3)
            ->values()
            ->all();
    }
}
