<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupportedCountryResource;
use App\Models\SupportedCountry;

class SupportedCountryController extends Controller
{
    public function index()
    {
        return SupportedCountryResource::collection(
            SupportedCountry::query()
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
        );
    }
}
