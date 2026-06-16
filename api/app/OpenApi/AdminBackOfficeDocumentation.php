<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminDashboard',
    type: 'object',
    properties: [
        new OA\Property(property: 'generated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Paris'),
        new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
        new OA\Property(property: 'low_stock_threshold', type: 'integer', example: 5),
        new OA\Property(property: 'kpis', type: 'object'),
        new OA\Property(property: 'catalog_health', type: 'object'),
        new OA\Property(property: 'stock_alerts', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'recent_activity', type: 'array', items: new OA\Items(type: 'object')),
    ],
)]
#[OA\Schema(
    schema: 'AdminInventoryProduct',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'sku', type: 'string', example: 'DEN-MIEL-250'),
        new OA\Property(property: 'slug', type: 'string', example: 'miel-de-montagne'),
        new OA\Property(property: 'name', ref: '#/components/schemas/LocalizedText'),
        new OA\Property(property: 'preview_name', type: 'object'),
        new OA\Property(property: 'category', type: 'object'),
        new OA\Property(property: 'stock_quantity', type: 'integer', example: 4),
        new OA\Property(property: 'stock_status', type: 'string', enum: ['inactive', 'out_of_stock', 'low_stock', 'in_stock']),
        new OA\Property(property: 'low_stock_threshold', type: 'integer', example: 5),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'price_cents', type: 'integer', example: 890),
        new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
        new OA\Property(property: 'variants', type: 'array', items: new OA\Items(type: 'object')),
    ],
)]
class AdminBackOfficeDocumentation
{
    #[OA\Get(
        path: '/api/v1/admin/dashboard',
        operationId: 'adminDashboard',
        security: [['sanctum' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['fr', 'en'])),
            new OA\Parameter(name: 'threshold', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 0, maximum: 100)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Back-office dashboard KPIs', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/AdminDashboard'),
            ])),
            new OA\Response(response: 403, description: 'Missing permission'),
        ],
    )]
    public function dashboard(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/admin/inventory',
        operationId: 'adminInventoryIndex',
        security: [['sanctum' => []]],
        tags: ['Admin Catalog'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['inactive', 'out_of_stock', 'low_stock', 'in_stock'])),
            new OA\Parameter(name: 'threshold', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 0, maximum: 100)),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['stock_asc', 'stock_desc', 'updated_asc'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 5, maximum: 100)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated inventory products', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AdminInventoryProduct')),
            ])),
        ],
    )]
    public function inventory(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/admin/products/{product}/publish',
        operationId: 'adminCatalogProductsPublish',
        security: [['sanctum' => []]],
        tags: ['Admin Catalog'],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product published', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/AdminProduct'),
            ])),
        ],
    )]
    public function publishProduct(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/admin/products/{product}/unpublish',
        operationId: 'adminCatalogProductsUnpublish',
        security: [['sanctum' => []]],
        tags: ['Admin Catalog'],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product unpublished', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/AdminProduct'),
            ])),
        ],
    )]
    public function unpublishProduct(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/admin/categories/{category}/activate',
        operationId: 'adminCatalogCategoriesActivate',
        security: [['sanctum' => []]],
        tags: ['Admin Catalog'],
        parameters: [
            new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category activated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/AdminCategory'),
            ])),
        ],
    )]
    public function activateCategory(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/admin/categories/{category}/deactivate',
        operationId: 'adminCatalogCategoriesDeactivate',
        security: [['sanctum' => []]],
        tags: ['Admin Catalog'],
        parameters: [
            new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category deactivated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/AdminCategory'),
            ])),
        ],
    )]
    public function deactivateCategory(): void
    {
    }
}
