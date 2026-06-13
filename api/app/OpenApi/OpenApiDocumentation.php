<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        version: '1.0.0',
        title: 'Denetfils API',
        description: 'Backend REST API for the Denetfils ecommerce platform: catalog, carts, identity, RBAC, GDPR consent and admin core.',
    ),
    servers: [
        new OA\Server(url: 'http://127.0.0.1:8000', description: 'Local API server'),
    ],
    tags: [
        new OA\Tag(name: 'System', description: 'Health and diagnostics'),
        new OA\Tag(name: 'Auth', description: 'Sanctum authentication'),
        new OA\Tag(name: 'Me', description: 'Authenticated user profile and addresses'),
        new OA\Tag(name: 'Catalog', description: 'Categories and localized products'),
        new OA\Tag(name: 'Cart', description: 'Guest cart API'),
        new OA\Tag(name: 'Europe', description: 'Supported countries and GDPR consent metadata'),
        new OA\Tag(name: 'Admin', description: 'Protected administration endpoints'),
    ],
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Sanctum bearer token. Use: Bearer <token>',
    scheme: 'bearer',
    bearerFormat: 'Sanctum token',
)]
#[OA\Schema(
    schema: 'ApiMessage',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Logged out.'),
    ],
)]
#[OA\Schema(
    schema: 'ValidationError',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(property: 'errors', type: 'object'),
    ],
)]
#[OA\Schema(
    schema: 'User',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Jean Martin'),
        new OA\Property(property: 'first_name', type: 'string', nullable: true, example: 'Jean'),
        new OA\Property(property: 'last_name', type: 'string', nullable: true, example: 'Martin'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jean@example.com'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+33123456789'),
        new OA\Property(property: 'preferred_locale', type: 'string', example: 'fr'),
        new OA\Property(property: 'country_code', type: 'string', nullable: true, example: 'FR'),
        new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Paris'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['customer']),
        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string'), example: []),
    ],
)]
#[OA\Schema(
    schema: 'AuthTokenResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'token', type: 'string', example: '1|plainTextSanctumToken'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
    ],
)]
#[OA\Schema(
    schema: 'Address',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'type', type: 'string', enum: ['billing', 'shipping'], example: 'billing'),
        new OA\Property(property: 'label', type: 'string', nullable: true, example: 'Home'),
        new OA\Property(property: 'recipient_name', type: 'string', example: 'Jean Martin'),
        new OA\Property(property: 'company', type: 'string', nullable: true, example: 'Denetfils'),
        new OA\Property(property: 'street_line_1', type: 'string', example: '10 Rue de Rivoli'),
        new OA\Property(property: 'street_line_2', type: 'string', nullable: true),
        new OA\Property(property: 'postal_code', type: 'string', example: '75001'),
        new OA\Property(property: 'city', type: 'string', example: 'Paris'),
        new OA\Property(property: 'region', type: 'string', nullable: true),
        new OA\Property(property: 'country_code', type: 'string', example: 'FR'),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
        new OA\Property(property: 'is_default', type: 'boolean', example: true),
    ],
)]
#[OA\Schema(
    schema: 'SupportedCountry',
    type: 'object',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'FR'),
        new OA\Property(property: 'name', type: 'string', example: 'France'),
        new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
        new OA\Property(property: 'default_locale', type: 'string', example: 'fr'),
        new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Paris'),
        new OA\Property(property: 'standard_vat_rate_percent', type: 'string', example: '20.00'),
        new OA\Property(property: 'food_vat_rate_percent', type: 'string', nullable: true, example: '5.50'),
        new OA\Property(property: 'is_eu', type: 'boolean', example: true),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
    ],
)]
#[OA\Schema(
    schema: 'PrivacyConsent',
    type: 'object',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'privacy_policy'),
        new OA\Property(property: 'version', type: 'string', example: '2026-06-13'),
        new OA\Property(property: 'required', type: 'boolean', example: true),
    ],
)]
#[OA\Schema(
    schema: 'Category',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'slug', type: 'string', example: 'epicerie-fine'),
        new OA\Property(property: 'name', type: 'string', example: 'Epicerie fine'),
        new OA\Property(property: 'products_count', type: 'integer', nullable: true, example: 3),
    ],
)]
#[OA\Schema(
    schema: 'Product',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'category', ref: '#/components/schemas/Category'),
        new OA\Property(property: 'name', type: 'string', example: 'Miel de montagne'),
        new OA\Property(property: 'slug', type: 'string', example: 'miel-de-montagne'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'origin', type: 'string', nullable: true, example: 'Origine France'),
        new OA\Property(property: 'sku', type: 'string', example: 'DEN-MIEL-250'),
        new OA\Property(property: 'price_cents', type: 'integer', example: 890),
        new OA\Property(property: 'formatted_price', type: 'string', example: '8,90 EUR'),
        new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
        new OA\Property(property: 'stock_quantity', type: 'integer', example: 35),
    ],
)]
#[OA\Schema(
    schema: 'Cart',
    type: 'object',
    properties: [
        new OA\Property(property: 'cart_token', type: 'string', example: 'guest-token'),
        new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
        new OA\Property(property: 'subtotal_cents', type: 'integer', example: 1780),
        new OA\Property(property: 'tax_cents', type: 'integer', example: 0),
        new OA\Property(property: 'total_cents', type: 'integer', example: 1780),
        new OA\Property(property: 'formatted_total', type: 'string', example: '17,80 EUR'),
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/CartItem')),
    ],
)]
#[OA\Schema(
    schema: 'CartItem',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'product_variant_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'unit_price_cents', type: 'integer', example: 890),
        new OA\Property(property: 'line_total_cents', type: 'integer', example: 1780),
    ],
)]
#[OA\Schema(
    schema: 'Role',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'admin'),
        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string'), example: ['users.view']),
    ],
)]
#[OA\Schema(
    schema: 'Permission',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'users.view'),
    ],
)]
#[OA\Schema(
    schema: 'AuditLog',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'action', type: 'string', example: 'users.created'),
        new OA\Property(property: 'auditable_type', type: 'string', nullable: true),
        new OA\Property(property: 'auditable_id', type: 'integer', nullable: true),
        new OA\Property(property: 'metadata', type: 'object', nullable: true),
        new OA\Property(property: 'ip_address', type: 'string', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ],
)]
#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['first_name', 'last_name', 'email', 'password', 'password_confirmation', 'country_code', 'privacy_policy_consent', 'terms_consent'],
    properties: [
        new OA\Property(property: 'first_name', type: 'string', example: 'Jean'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Martin'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jean@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password-secure'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password-secure'),
        new OA\Property(property: 'preferred_locale', type: 'string', example: 'fr'),
        new OA\Property(property: 'country_code', type: 'string', example: 'FR'),
        new OA\Property(property: 'privacy_policy_consent', type: 'boolean', example: true),
        new OA\Property(property: 'terms_consent', type: 'boolean', example: true),
        new OA\Property(property: 'marketing_consent', type: 'boolean', example: false),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@denetfils.fr'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
        new OA\Property(property: 'device_name', type: 'string', example: 'Swagger UI'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'AddressRequest',
    required: ['type', 'recipient_name', 'street_line_1', 'postal_code', 'city', 'country_code'],
    properties: [
        new OA\Property(property: 'type', type: 'string', enum: ['billing', 'shipping'], example: 'billing'),
        new OA\Property(property: 'label', type: 'string', example: 'Home'),
        new OA\Property(property: 'recipient_name', type: 'string', example: 'Jean Martin'),
        new OA\Property(property: 'street_line_1', type: 'string', example: '10 Rue de Rivoli'),
        new OA\Property(property: 'postal_code', type: 'string', example: '75001'),
        new OA\Property(property: 'city', type: 'string', example: 'Paris'),
        new OA\Property(property: 'country_code', type: 'string', example: 'FR'),
        new OA\Property(property: 'is_default', type: 'boolean', example: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CartItemRequest',
    required: ['product_id', 'quantity'],
    properties: [
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'product_variant_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 1),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'QuantityRequest',
    required: ['quantity'],
    properties: [
        new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 2),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'AdminUserRequest',
    required: ['first_name', 'last_name', 'email', 'password', 'country_code'],
    properties: [
        new OA\Property(property: 'first_name', type: 'string', example: 'Support'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Agent'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'support@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password-secure'),
        new OA\Property(property: 'country_code', type: 'string', example: 'FR'),
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['support_agent']),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'AssignRolesRequest',
    required: ['roles'],
    properties: [
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['support_agent']),
    ],
    type: 'object',
)]
class OpenApiDocumentation
{
    #[OA\Get(
        path: '/api/v1/health',
        operationId: 'health',
        tags: ['System'],
        responses: [
            new OA\Response(response: 200, description: 'API health status'),
        ],
    )]
    public function health(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/auth/register',
        operationId: 'authRegister',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Registered customer', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/AuthTokenResponse'),
            ])),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function authRegister(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/auth/login',
        operationId: 'authLogin',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Authenticated user token', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/AuthTokenResponse'),
            ])),
            new OA\Response(response: 403, description: 'Inactive account'),
            new OA\Response(response: 422, description: 'Invalid credentials'),
        ],
    )]
    public function authLogin(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/auth/logout',
        operationId: 'authLogout',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Token revoked', content: new OA\JsonContent(ref: '#/components/schemas/ApiMessage')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function authLogout(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/auth/me',
        operationId: 'authMe',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Authenticated user', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/User'),
            ])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function authMe(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/supported-countries',
        operationId: 'supportedCountries',
        tags: ['Europe'],
        parameters: [
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['fr', 'en'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Supported countries', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/SupportedCountry')),
            ])),
        ],
    )]
    public function supportedCountries(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/privacy/consents/current',
        operationId: 'currentPrivacyConsents',
        tags: ['Europe'],
        responses: [
            new OA\Response(response: 200, description: 'Current consent versions', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PrivacyConsent')),
            ])),
        ],
    )]
    public function currentPrivacyConsents(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/categories',
        operationId: 'categoriesIndex',
        tags: ['Catalog'],
        parameters: [
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['fr', 'en'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Localized categories', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Category')),
            ])),
        ],
    )]
    public function categoriesIndex(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/products',
        operationId: 'productsIndex',
        tags: ['Catalog'],
        parameters: [
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['fr', 'en'])),
            new OA\Parameter(name: 'category', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'boissons-naturelles'),
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'hibiscus'),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['default', 'latest', 'price_asc', 'price_desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Localized products', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
            ])),
        ],
    )]
    public function productsIndex(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/products/{slug}',
        operationId: 'productsShow',
        tags: ['Catalog'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'miel-de-montagne'),
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['fr', 'en'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product detail', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Product'),
            ])),
            new OA\Response(response: 404, description: 'Product not found'),
        ],
    )]
    public function productsShow(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/carts',
        operationId: 'cartsStore',
        tags: ['Cart'],
        responses: [
            new OA\Response(response: 201, description: 'Guest cart created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
            ])),
        ],
    )]
    public function cartsStore(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/carts/{cartToken}',
        operationId: 'cartsShow',
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'cartToken', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['fr', 'en'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Guest cart', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
            ])),
            new OA\Response(response: 404, description: 'Cart not found'),
        ],
    )]
    public function cartsShow(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/carts/{cartToken}/items',
        operationId: 'cartItemsStore',
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'cartToken', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CartItemRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Cart item added', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function cartItemsStore(): void
    {
    }

    #[OA\Patch(
        path: '/api/v1/carts/{cartToken}/items/{item}',
        operationId: 'cartItemsUpdate',
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'cartToken', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'item', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/QuantityRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Cart item updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
            ])),
        ],
    )]
    public function cartItemsUpdate(): void
    {
    }

    #[OA\Delete(
        path: '/api/v1/carts/{cartToken}/items/{item}',
        operationId: 'cartItemsDestroy',
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'cartToken', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'item', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cart item removed', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
            ])),
        ],
    )]
    public function cartItemsDestroy(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/me',
        operationId: 'meShow',
        security: [['sanctum' => []]],
        tags: ['Me'],
        responses: [
            new OA\Response(response: 200, description: 'Current user profile', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/User'),
            ])),
        ],
    )]
    public function meShow(): void
    {
    }

    #[OA\Patch(
        path: '/api/v1/me',
        operationId: 'meUpdate',
        security: [['sanctum' => []]],
        tags: ['Me'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'first_name', type: 'string', example: 'Jean'),
            new OA\Property(property: 'last_name', type: 'string', example: 'Martin'),
            new OA\Property(property: 'preferred_locale', type: 'string', example: 'en'),
            new OA\Property(property: 'country_code', type: 'string', example: 'DE'),
            new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Berlin'),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Updated user profile', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/User'),
            ])),
        ],
    )]
    public function meUpdate(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/me/addresses',
        operationId: 'meAddressesIndex',
        security: [['sanctum' => []]],
        tags: ['Me'],
        responses: [
            new OA\Response(response: 200, description: 'Current user addresses', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Address')),
            ])),
        ],
    )]
    public function meAddressesIndex(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/me/addresses',
        operationId: 'meAddressesStore',
        security: [['sanctum' => []]],
        tags: ['Me'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AddressRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Address created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Address'),
            ])),
        ],
    )]
    public function meAddressesStore(): void
    {
    }

    #[OA\Patch(
        path: '/api/v1/me/addresses/{address}',
        operationId: 'meAddressesUpdate',
        security: [['sanctum' => []]],
        tags: ['Me'],
        parameters: [
            new OA\Parameter(name: 'address', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AddressRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Address updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Address'),
            ])),
        ],
    )]
    public function meAddressesUpdate(): void
    {
    }

    #[OA\Delete(
        path: '/api/v1/me/addresses/{address}',
        operationId: 'meAddressesDestroy',
        security: [['sanctum' => []]],
        tags: ['Me'],
        parameters: [
            new OA\Parameter(name: 'address', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Address deleted', content: new OA\JsonContent(ref: '#/components/schemas/ApiMessage')),
        ],
    )]
    public function meAddressesDestroy(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/admin/users',
        operationId: 'adminUsersIndex',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        responses: [
            new OA\Response(response: 200, description: 'Paginated users', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')),
            ])),
        ],
    )]
    public function adminUsersIndex(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/admin/users',
        operationId: 'adminUsersStore',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AdminUserRequest')),
        responses: [
            new OA\Response(response: 201, description: 'User created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/User'),
            ])),
        ],
    )]
    public function adminUsersStore(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/admin/users/{user}',
        operationId: 'adminUsersShow',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User detail', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/User'),
            ])),
        ],
    )]
    public function adminUsersShow(): void
    {
    }

    #[OA\Patch(
        path: '/api/v1/admin/users/{user}',
        operationId: 'adminUsersUpdate',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AdminUserRequest')),
        responses: [
            new OA\Response(response: 200, description: 'User updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/User'),
            ])),
        ],
    )]
    public function adminUsersUpdate(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/admin/users/{user}/roles',
        operationId: 'adminUsersAssignRoles',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AssignRolesRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Roles assigned', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/User'),
            ])),
        ],
    )]
    public function adminUsersAssignRoles(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/admin/users/{user}/suspend',
        operationId: 'adminUsersSuspend',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User suspended', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/User'),
            ])),
        ],
    )]
    public function adminUsersSuspend(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/admin/roles',
        operationId: 'adminRolesIndex',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        responses: [
            new OA\Response(response: 200, description: 'Roles', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Role')),
            ])),
        ],
    )]
    public function adminRolesIndex(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/admin/permissions',
        operationId: 'adminPermissionsIndex',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        responses: [
            new OA\Response(response: 200, description: 'Permissions', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Permission')),
            ])),
        ],
    )]
    public function adminPermissionsIndex(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/admin/audit-logs',
        operationId: 'adminAuditLogsIndex',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        responses: [
            new OA\Response(response: 200, description: 'Audit logs', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AuditLog')),
            ])),
        ],
    )]
    public function adminAuditLogsIndex(): void
    {
    }
}
