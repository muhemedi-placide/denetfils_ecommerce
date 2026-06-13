<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

Route::get('/v1/health', function () {
    return response()->json([
        'service' => 'denetfils-api',
        'status' => 'ok',
        'version' => 'v1',
    ]);
});

Route::prefix('v1')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::post('/carts', [CartController::class, 'store']);
    Route::get('/carts/{cartToken}', [CartController::class, 'show']);
    Route::post('/carts/{cartToken}/items', [CartController::class, 'addItem']);
    Route::patch('/carts/{cartToken}/items/{item}', [CartController::class, 'updateItem']);
    Route::delete('/carts/{cartToken}/items/{item}', [CartController::class, 'destroyItem']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
