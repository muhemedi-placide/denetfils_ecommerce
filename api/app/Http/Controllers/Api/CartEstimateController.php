<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Checkout\EstimateRequest;
use App\Services\Checkout\CheckoutQuoteService;
use Illuminate\Http\JsonResponse;

class CartEstimateController extends Controller
{
    public function store(
        EstimateRequest $request,
        string $cartToken,
        CheckoutQuoteService $quotes,
    ): JsonResponse {
        $data = $request->validated();

        return response()->json([
            'data' => $quotes->estimate(
                $cartToken,
                strtoupper($data['country_code']),
                $data['locale'] ?? 'fr',
            ),
        ]);
    }
}
