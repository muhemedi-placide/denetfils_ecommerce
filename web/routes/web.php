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

Route::get('/{locale}/livraison', [ShopController::class, 'delivery'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.delivery');

Route::get('/{locale}/mentions-legales', [ShopController::class, 'legalNotice'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.legal');

Route::get('/{locale}/conditions-utilisation', [ShopController::class, 'terms'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.terms');

Route::get('/{locale}/paiement-securise', [ShopController::class, 'securePayment'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.payment');

Route::get('/{locale}/products/{slug}', [ShopController::class, 'show'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('products.show');

Route::get('/{locale}', [ShopController::class, 'home'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('home.localized');
