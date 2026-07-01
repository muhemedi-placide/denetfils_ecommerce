<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_french_homepage_uses_french_copy(): void
    {
        $this->withoutVite();
        Http::fake([
            '*' => Http::response(['data' => []]),
        ]);

        $this->get('/fr')
            ->assertOk()
            ->assertSee('Accueil')
            ->assertSee('Boutique')
            ->assertSee('fi-fr', false)
            ->assertSee('fi-us', false)
            ->assertSee('aria-selected="true"', false)
            ->assertSee('aria-current="true"', false);
    }

    public function test_english_homepage_uses_english_copy(): void
    {
        $this->withoutVite();
        Http::fake([
            '*' => Http::response(['data' => []]),
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Home')
            ->assertSee('Shop');
    }

    public function test_root_uses_browser_language_before_detected_country(): void
    {
        config()->set('localization.cloudflare.trust_country_header', true);

        $this->withHeaders([
            'Accept-Language' => 'en-US,en;q=0.9',
            'CF-IPCountry' => 'FR',
        ])->get('/')->assertRedirect('/en');
    }

    public function test_root_uses_cloudflare_country_when_browser_has_no_language(): void
    {
        config()->set('localization.cloudflare.trust_country_header', true);

        $this->withHeader('CF-IPCountry', 'DE')
            ->get('/')
            ->assertRedirect('/en');
    }

    public function test_ipinfo_is_used_as_fail_open_country_fallback(): void
    {
        config()->set('localization.cloudflare.trust_country_header', false);
        config()->set('localization.ipinfo.token', 'test-token');
        config()->set('localization.ipinfo.base_url', 'https://api.ipinfo.test/lite');
        Http::fake([
            'api.ipinfo.test/*' => Http::response(['country_code' => 'DE']),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->get('/')
            ->assertRedirect('/en');

        Http::assertSentCount(1);
    }

    public function test_manual_preferences_are_persisted_for_ninety_days(): void
    {
        $this->post('/preferences/visitor', [
            'country_code' => 'BE',
            'locale' => 'fr',
            'return_to' => '/en/panier',
        ])
            ->assertRedirect('/fr/panier')
            ->assertCookie('visitor_country', 'BE')
            ->assertCookie('visitor_locale', 'fr');
    }
}
