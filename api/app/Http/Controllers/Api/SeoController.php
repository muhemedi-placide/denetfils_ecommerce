<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Seo\SeoPayloadBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function site(Request $request, SeoPayloadBuilder $seo): JsonResponse
    {
        return response()->json([
            'data' => $seo->site($this->locale($request)),
        ]);
    }

    public function robots(SeoPayloadBuilder $seo): Response
    {
        return response($seo->robots(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(SeoPayloadBuilder $seo): Response
    {
        return response($seo->sitemap(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function locale(Request $request): string
    {
        $locale = $request->query('locale', 'fr');

        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }
}
