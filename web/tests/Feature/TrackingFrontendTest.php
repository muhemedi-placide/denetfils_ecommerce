<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrackingFrontendTest extends TestCase
{
    public function test_tracking_page_displays_mondial_relay_events_from_api(): void
    {
        $this->withoutVite();
        Http::fake([
            '*/shipping/tracking' => Http::response(['data' => [
                'source' => 'mondial_relay',
                'carrier_code' => 'mondial_relay',
                'tracking_number' => '123456789012',
                'status_label' => 'Colis pris en charge',
                'delivered' => false,
                'events' => [[
                    'label' => 'Colis pris en charge',
                    'date' => '23/06/2026',
                    'time' => '10:15',
                    'location' => 'Paris',
                ]],
            ]], 200),
        ]);

        $this->get('/fr/suivi-colis?tracking_number=123456789012')
            ->assertOk()
            ->assertSee('Suivi de colis')
            ->assertSee('123456789012')
            ->assertSee('Colis pris en charge')
            ->assertSee('Paris');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/shipping/tracking')
            && $request['tracking_number'] === '123456789012'
            && $request['locale'] === 'fr');
    }
}
