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
            ->assertSee('Boutique');
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
}
