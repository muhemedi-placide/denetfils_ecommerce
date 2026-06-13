<?php

use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'home'])->name('home');

Route::get('/{locale}/products/{slug}', [ShopController::class, 'show'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('products.show');

Route::get('/{locale}', [ShopController::class, 'home'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('home.localized');
