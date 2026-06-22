<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Checkout\PickupPointRequest;
use App\Services\Logistics\PickupPointSearchService;
use Illuminate\Http\JsonResponse;

class PickupPointController extends Controller
{
    public function store(PickupPointRequest $request, PickupPointSearchService $pickupPoints): JsonResponse
    {
        return response()->json([
            'data' => $pickupPoints->searchForUser($request->user(), $request->validated()),
        ]);
    }
}
