<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CoreDefaults;
use Illuminate\Http\JsonResponse;

class PrivacyConsentController extends Controller
{
    public function current(): JsonResponse
    {
        return response()->json([
            'data' => collect(CoreDefaults::CONSENT_VERSIONS)
                ->map(fn (string $version, string $type) => [
                    'type' => $type,
                    'version' => $version,
                    'required' => in_array($type, ['privacy_policy', 'terms'], true),
                ])
                ->values()
                ->all(),
        ]);
    }
}
