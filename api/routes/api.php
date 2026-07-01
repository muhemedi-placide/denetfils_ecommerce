<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\CatalogCategoryController;
use App\Http\Controllers\Api\Admin\CatalogProductController;
use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\CartController as AdminCartController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\InventoryController;
use App\Http\Controllers\Api\Admin\InvoiceController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\OrderConversationController as AdminOrderConversationController;
use App\Http\Controllers\Api\Admin\PaymentMethodController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\ShippingCarrierController;
use App\Http\Controllers\Api\Admin\ShipmentController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartEstimateController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutQuoteController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderConversationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PrivacyConsentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SeoController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\SupportedCountryController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', function () {
    return response()->json([
        'service' => \Illuminate\Support\Str::slug(config('shop.name')).'-api',
        'status' => 'ok',
        'version' => 'v1',
    ]);
});

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/admin/auth/login', [AdminAuthController::class, 'login']);
    Route::get('/seo/site', [SeoController::class, 'site']);
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
    Route::get('/robots.txt', [SeoController::class, 'robots']);
    Route::get('/supported-countries', [SupportedCountryController::class, 'index']);
    Route::get('/privacy/consents/current', [PrivacyConsentController::class, 'current']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::post('/shipping/tracking', [ShippingController::class, 'tracking'])->middleware('throttle:shipment-tracking');
    Route::post('/payments/stripe/webhook', [PaymentController::class, 'stripeWebhook']);
    Route::post('/payments/paypal/webhook', [PaymentController::class, 'paypalWebhook']);
    Route::post('/payments/paypal/express/orders', [PaymentController::class, 'createPaypalExpressOrder'])->middleware('throttle:20,1');
    Route::post('/payments/paypal/express/finalize', [PaymentController::class, 'finalizePaypalExpressOrder'])->middleware('throttle:20,1');

    Route::post('/carts', [CartController::class, 'store']);
    Route::get('/carts/{cartToken}', [CartController::class, 'show']);
    Route::post('/carts/{cartToken}/items', [CartController::class, 'addItem']);
    Route::patch('/carts/{cartToken}/items/{item}', [CartController::class, 'updateItem']);
    Route::delete('/carts/{cartToken}/items/{item}', [CartController::class, 'destroyItem']);
    Route::post('/carts/{cartToken}/recovery-links', [CartController::class, 'createRecoveryLink'])->middleware('throttle:20,1');
    Route::get('/cart-recoveries/{recoveryToken}', [CartController::class, 'recover'])->middleware('throttle:60,1');
    Route::post('/carts/{cartToken}/estimate', [CartEstimateController::class, 'store'])->middleware('throttle:60,1');

    Route::middleware(['auth:sanctum', 'customer', 'active.user'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/me', [MeController::class, 'show']);
        Route::patch('/me', [MeController::class, 'update']);
        Route::get('/me/orders', [OrderController::class, 'index']);
        Route::get('/me/addresses', [AddressController::class, 'index']);
        Route::post('/me/addresses', [AddressController::class, 'store']);
        Route::patch('/me/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/me/addresses/{address}', [AddressController::class, 'destroy']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::get('/orders/{order}/conversation', [OrderConversationController::class, 'show']);
        Route::post('/orders/{order}/conversation/open', [OrderConversationController::class, 'open']);
        Route::post('/orders/{order}/conversation/messages', [OrderConversationController::class, 'storeMessage']);
        Route::post('/orders/{order}/conversation/read', [OrderConversationController::class, 'markRead']);
        Route::post('/orders/{order}/conversation/close', [OrderConversationController::class, 'close']);
        Route::post('/orders/{order}/payments/stripe/payment-intent', [PaymentController::class, 'createStripePaymentIntent']);
        Route::post('/orders/{order}/payments/stripe/payment-intent/confirm', [PaymentController::class, 'confirmStripePaymentIntent']);
        Route::post('/orders/{order}/payments/paypal/orders', [PaymentController::class, 'createPaypalOrder']);
        Route::post('/orders/{order}/payments/paypal/orders/{paypalOrderId}/capture', [PaymentController::class, 'capturePaypalOrder']);
        Route::post('/checkout/quote', [CheckoutQuoteController::class, 'store']);
        Route::get('/shipping/methods', [ShippingController::class, 'methods']);
        Route::post('/shipping/pickup-points/search', [ShippingController::class, 'pickupPoints'])->middleware('throttle:pickup-search');
        Route::post('/shipping/pickup-points/detail', [ShippingController::class, 'pickupPointDetail'])->middleware('throttle:pickup-search');
        Route::post('/shipping/postal-codes/search', [ShippingController::class, 'postalCodes'])->middleware('throttle:pickup-search');
        Route::post('/shipping/selection', [ShippingController::class, 'select']);

    });

    Route::middleware(['auth:sanctum', 'system.user', 'active.user'])->prefix('admin')->group(function () {
        Route::get('/auth/me', [AdminAuthController::class, 'me']);
        Route::post('/auth/logout', [AdminAuthController::class, 'logout']);
            Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:catalog.view');
            Route::get('/inventory', [InventoryController::class, 'index'])->middleware('permission:catalog.view');

            Route::get('/orders', [AdminOrderController::class, 'index'])->middleware('permission:orders.view');
            Route::post('/orders', [AdminOrderController::class, 'store'])->middleware('permission:orders.manage');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->middleware('permission:orders.view');
            Route::patch('/orders/{order}', [AdminOrderController::class, 'update'])->middleware('permission:orders.manage');
            Route::get('/orders/{order}/conversation', [AdminOrderConversationController::class, 'show'])->middleware('permission:orders.view');
            Route::post('/orders/{order}/conversation/open', [AdminOrderConversationController::class, 'open'])->middleware('permission:orders.manage');
            Route::post('/orders/{order}/conversation/messages', [AdminOrderConversationController::class, 'storeMessage'])->middleware('permission:orders.manage');
            Route::post('/orders/{order}/conversation/read', [AdminOrderConversationController::class, 'markRead'])->middleware('permission:orders.view');
            Route::post('/orders/{order}/conversation/close', [AdminOrderConversationController::class, 'close'])->middleware('permission:orders.manage');
            Route::post('/orders/{order}/shipment/create', [ShipmentController::class, 'create'])->middleware('permission:orders.manage');
            Route::get('/orders/{order}/shipments/{shipment}/label', [ShipmentController::class, 'label'])->middleware('permission:orders.view');
            Route::get('/invoices', [InvoiceController::class, 'index'])->middleware('permission:orders.view');
            Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('permission:orders.view');
            Route::get('/carts', [AdminCartController::class, 'index'])->middleware('permission:orders.view');
            Route::get('/carts/{cart}', [AdminCartController::class, 'show'])->middleware('permission:orders.view');
            Route::post('/carts/{cart}/recovery-links', [AdminCartController::class, 'createRecoveryLink'])->middleware('permission:orders.manage');

            Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view');
            Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create');
            Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:users.view');
            Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.update');
            Route::post('/users/{user}/roles', [UserController::class, 'assignRoles'])->middleware('permission:roles.assign');
            Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->middleware('permission:users.suspend');

            Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customers.view');
            Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('permission:customers.view');
            Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers.manage');

            Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
            Route::patch('/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->middleware('permission:roles.assign');
            Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view');
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit.view');

            Route::get('/payment-methods/schemas', [PaymentMethodController::class, 'schemas'])->middleware('permission:payments.view');
            Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->middleware('permission:payments.view');
            Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->middleware('permission:payments.manage');
            Route::get('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'show'])->middleware('permission:payments.view');
            Route::patch('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])->middleware('permission:payments.manage');
            Route::post('/payment-methods/{paymentMethod}/activate', [PaymentMethodController::class, 'activate'])->middleware('permission:payments.manage');
            Route::post('/payment-methods/{paymentMethod}/deactivate', [PaymentMethodController::class, 'deactivate'])->middleware('permission:payments.manage');
            Route::post('/payment-methods/{paymentMethod}/test-connection', [PaymentMethodController::class, 'testConnection'])->middleware('permission:payments.manage');

            Route::get('/shipping-carriers/schemas', [ShippingCarrierController::class, 'schemas'])->middleware('permission:payments.view');
            Route::get('/shipping-carriers', [ShippingCarrierController::class, 'index'])->middleware('permission:payments.view');
            Route::post('/shipping-carriers', [ShippingCarrierController::class, 'store'])->middleware('permission:payments.manage');
            Route::get('/shipping-carriers/{shippingCarrier}', [ShippingCarrierController::class, 'show'])->middleware('permission:payments.view');
            Route::patch('/shipping-carriers/{shippingCarrier}', [ShippingCarrierController::class, 'update'])->middleware('permission:payments.manage');
            Route::post('/shipping-carriers/{shippingCarrier}/activate', [ShippingCarrierController::class, 'activate'])->middleware('permission:payments.manage');
            Route::post('/shipping-carriers/{shippingCarrier}/deactivate', [ShippingCarrierController::class, 'deactivate'])->middleware('permission:payments.manage');
            Route::post('/shipping-carriers/{shippingCarrier}/test-connection', [ShippingCarrierController::class, 'testConnection'])->middleware('permission:payments.manage');

            Route::get('/categories', [CatalogCategoryController::class, 'index'])->middleware('permission:catalog.view');
            Route::post('/categories', [CatalogCategoryController::class, 'store'])->middleware('permission:catalog.manage');
            Route::get('/categories/{category}', [CatalogCategoryController::class, 'show'])->middleware('permission:catalog.view');
            Route::patch('/categories/{category}', [CatalogCategoryController::class, 'update'])->middleware('permission:catalog.manage');
            Route::post('/categories/{category}/activate', [CatalogCategoryController::class, 'activate'])->middleware('permission:catalog.manage');
            Route::post('/categories/{category}/deactivate', [CatalogCategoryController::class, 'deactivate'])->middleware('permission:catalog.manage');

            Route::get('/products', [CatalogProductController::class, 'index'])->middleware('permission:catalog.view');
            Route::post('/products', [CatalogProductController::class, 'store'])->middleware('permission:catalog.manage');
            Route::get('/products/{product}', [CatalogProductController::class, 'show'])->middleware('permission:catalog.view');
            Route::patch('/products/{product}', [CatalogProductController::class, 'update'])->middleware('permission:catalog.manage');
            Route::post('/products/{product}/publish', [CatalogProductController::class, 'publish'])->middleware('permission:catalog.manage');
            Route::post('/products/{product}/unpublish', [CatalogProductController::class, 'unpublish'])->middleware('permission:catalog.manage');
    });
});
