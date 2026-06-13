<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('short_description')->nullable()->after('description');
            $table->json('highlights')->nullable()->after('origin');
            $table->json('badges')->nullable()->after('highlights');
            $table->json('tags')->nullable()->after('badges');
            $table->json('ingredients')->nullable()->after('tags');
            $table->json('allergens')->nullable()->after('ingredients');
            $table->json('nutrition_facts')->nullable()->after('allergens');
            $table->json('certifications')->nullable()->after('nutrition_facts');
            $table->json('storage_instructions')->nullable()->after('certifications');
            $table->json('usage_instructions')->nullable()->after('storage_instructions');
            $table->json('shipping_profile')->nullable()->after('usage_instructions');
            $table->json('return_policy')->nullable()->after('shipping_profile');
            $table->json('guarantee')->nullable()->after('return_policy');
            $table->unsignedInteger('max_order_quantity')->nullable()->after('stock_quantity');
            $table->decimal('rating_average', 3, 2)->default(0)->after('max_order_quantity');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_average');
            $table->unsignedInteger('sales_count')->default(0)->after('rating_count');
            $table->json('seo_title')->nullable()->after('sales_count');
            $table->json('seo_description')->nullable()->after('seo_title');
            $table->json('seo_keywords')->nullable()->after('seo_description');
            $table->string('canonical_path')->nullable()->after('seo_keywords');
            $table->timestamp('published_at')->nullable()->after('canonical_path');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->unsignedInteger('width')->nullable()->after('url');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->string('dominant_color', 16)->nullable()->after('height');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['width', 'height', 'dominant_color']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'short_description',
                'highlights',
                'badges',
                'tags',
                'ingredients',
                'allergens',
                'nutrition_facts',
                'certifications',
                'storage_instructions',
                'usage_instructions',
                'shipping_profile',
                'return_policy',
                'guarantee',
                'max_order_quantity',
                'rating_average',
                'rating_count',
                'sales_count',
                'seo_title',
                'seo_description',
                'seo_keywords',
                'canonical_path',
                'published_at',
            ]);
        });
    }
};
