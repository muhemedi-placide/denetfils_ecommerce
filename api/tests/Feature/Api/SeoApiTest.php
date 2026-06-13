<?php

namespace Tests\Feature\Api;

use Database\Seeders\EcommerceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_seo_endpoint_returns_foundation_structured_data(): void
    {
        $response = $this->getJson('/api/v1/seo/site?locale=fr');

        $response
            ->assertOk()
            ->assertJsonPath('data.json_ld.organization.@type', 'Organization')
            ->assertJsonPath('data.json_ld.website.@type', 'WebSite')
            ->assertJsonPath('data.json_ld.articles.0.@type', 'Article');
    }

    public function test_robots_txt_exposes_sitemap_and_blocks_private_api_paths(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();

        $content = $response->getContent();

        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /api/v1/admin', $content);
        $this->assertStringContainsString('Sitemap: http://127.0.0.1:8000/sitemap.xml', $content);
    }

    public function test_sitemap_xml_contains_localized_product_urls_and_hreflang(): void
    {
        $this->seed(EcommerceSeeder::class);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();

        $content = $response->getContent();

        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('/fr/products/miel-de-montagne', $content);
        $this->assertStringContainsString('/en/products/miel-de-montagne', $content);
        $this->assertStringContainsString('hreflang="fr-FR"', $content);
        $this->assertStringContainsString('hreflang="x-default"', $content);
    }
}
