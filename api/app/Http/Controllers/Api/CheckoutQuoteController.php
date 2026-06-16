<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Checkout\QuoteRequest;
use App\Services\Checkout\CheckoutQuoteService;
use Illuminate\Http\JsonResponse;

class CheckoutQuoteController extends Controller
{
    public function store(QuoteRequest $request, CheckoutQuoteService $quotes): JsonResponse
    {
        return response()->json([
            'data' => $quotes->quoteForUser($request->user(), $request->validated()),
        ]);
    }
}
