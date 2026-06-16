<?php

use App\Http\Controllers\Admin\BackOfficeController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [ShopController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [ShopController::class, 'sitemap'])->name('seo.sitemap');

Route::get('/', [ShopController::class, 'home'])->name('home');

Route::get('/{locale}/about', [ShopController::class, 'about'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.about');

Route::get('/{locale}/blog', [ShopController::class, 'blog'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('blog.index');

Route::get('/{locale}/blog/{slug}', [ShopController::class, 'blogShow'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('blog.show');

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

Route::prefix('/{locale}/admin')
    ->whereIn('locale', ['fr', 'en'])
    ->group(function () {
        Route::get('/connexion', [BackOfficeController::class, 'loginForm'])->name('admin.login');
        Route::post('/connexion', [BackOfficeController::class, 'login'])->name('admin.login.store');
        Route::post('/deconnexion', [BackOfficeController::class, 'logout'])->name('admin.logout');

        Route::get('/', [BackOfficeController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/catalogue', [BackOfficeController::class, 'catalog'])->name('admin.catalog');
        Route::get('/stock', [BackOfficeController::class, 'inventory'])->name('admin.inventory');
        Route::get('/utilisateurs', [BackOfficeController::class, 'users'])->name('admin.users');
        Route::get('/acces', [BackOfficeController::class, 'access'])->name('admin.access');
        Route::get('/audit', [BackOfficeController::class, 'audit'])->name('admin.audit');
    });

Route::get('/{locale}/connexion', [CustomerAccountController::class, 'loginForm'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.login');

Route::post('/{locale}/connexion', [CustomerAccountController::class, 'login'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.login.store');

Route::get('/{locale}/inscription', [CustomerAccountController::class, 'registerForm'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.register');

Route::post('/{locale}/inscription', [CustomerAccountController::class, 'register'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.register.store');

Route::post('/{locale}/deconnexion', [CustomerAccountController::class, 'logout'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.logout');

Route::get('/{locale}/mon-compte', [CustomerAccountController::class, 'show'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.show');

Route::patch('/{locale}/mon-compte', [CustomerAccountController::class, 'updateProfile'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.update');

Route::post('/{locale}/mon-compte/adresses', [CustomerAccountController::class, 'storeAddress'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.addresses.store');

Route::patch('/{locale}/mon-compte/adresses/{address}', [CustomerAccountController::class, 'updateAddress'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.addresses.update');

Route::delete('/{locale}/mon-compte/adresses/{address}', [CustomerAccountController::class, 'deleteAddress'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('account.addresses.delete');

Route::get('/{locale}/panier', [ShopController::class, 'cart'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('cart.show');

Route::get('/{locale}/commande', [ShopController::class, 'checkout'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('checkout.show');

Route::get('/{locale}/products/{slug}', [ShopController::class, 'show'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('products.show');

Route::get('/{locale}', [ShopController::class, 'home'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('home.localized');
