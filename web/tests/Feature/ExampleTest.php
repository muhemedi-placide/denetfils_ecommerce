<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->withoutVite();
        Http::fake([
            '*' => Http::response(['data' => []]),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
