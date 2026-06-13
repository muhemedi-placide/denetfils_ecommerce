<?php

use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'home'])->name('home');

Route::get('/{locale}/about', [ShopController::class, 'about'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.about');

Route::get('/{locale}/blog', [ShopController::class, 'blog'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('blog.index');

Route::get('/{locale}/products/{slug}', [ShopController::class, 'show'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('products.show');

Route::get('/{locale}', [ShopController::class, 'home'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('home.localized');
