<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\PrivacyConsentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SeoController;
use App\Http\Controllers\Api\SupportedCountryController;
use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\CatalogCategoryController;
use App\Http\Controllers\Api\Admin\CatalogProductController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\UserController;

Route::get('/v1/health', function () {
    return response()->json([
        'service' => 'denetfils-api',
        'status' => 'ok',
        'version' => 'v1',
    ]);
});

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/seo/site', [SeoController::class, 'site']);
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
    Route::get('/robots.txt', [SeoController::class, 'robots']);
    Route::get('/supported-countries', [SupportedCountryController::class, 'index']);
    Route::get('/privacy/consents/current', [PrivacyConsentController::class, 'current']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::post('/carts', [CartController::class, 'store']);
    Route::get('/carts/{cartToken}', [CartController::class, 'show']);
    Route::post('/carts/{cartToken}/items', [CartController::class, 'addItem']);
    Route::patch('/carts/{cartToken}/items/{item}', [CartController::class, 'updateItem']);
    Route::delete('/carts/{cartToken}/items/{item}', [CartController::class, 'destroyItem']);

    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/me', [MeController::class, 'show']);
        Route::patch('/me', [MeController::class, 'update']);
        Route::get('/me/addresses', [AddressController::class, 'index']);
        Route::post('/me/addresses', [AddressController::class, 'store']);
        Route::patch('/me/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/me/addresses/{address}', [AddressController::class, 'destroy']);

        Route::prefix('admin')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view');
            Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create');
            Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:users.view');
            Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.update');
            Route::post('/users/{user}/roles', [UserController::class, 'assignRoles'])->middleware('permission:roles.assign');
            Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->middleware('permission:users.suspend');

            Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
            Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view');
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit.view');

            Route::get('/categories', [CatalogCategoryController::class, 'index'])->middleware('permission:catalog.view');
            Route::post('/categories', [CatalogCategoryController::class, 'store'])->middleware('permission:catalog.manage');
            Route::get('/categories/{category}', [CatalogCategoryController::class, 'show'])->middleware('permission:catalog.view');
            Route::patch('/categories/{category}', [CatalogCategoryController::class, 'update'])->middleware('permission:catalog.manage');

            Route::get('/products', [CatalogProductController::class, 'index'])->middleware('permission:catalog.view');
            Route::post('/products', [CatalogProductController::class, 'store'])->middleware('permission:catalog.manage');
            Route::get('/products/{product}', [CatalogProductController::class, 'show'])->middleware('permission:catalog.view');
            Route::patch('/products/{product}', [CatalogProductController::class, 'update'])->middleware('permission:catalog.manage');
        });
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
