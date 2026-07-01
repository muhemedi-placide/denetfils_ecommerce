<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('purchase_price_cents')->nullable()->after('sku');
            $table->unsignedInteger('compare_at_price_cents')->nullable()->after('price_cents');
            $table->boolean('price_includes_tax')->default(true)->after('currency');
            $table->string('barcode', 64)->nullable()->unique()->after('sku');
            $table->string('brand', 120)->nullable()->after('barcode');
            $table->string('supplier_reference', 120)->nullable()->after('brand');
            $table->string('unit_label', 40)->nullable()->after('weight_grams');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->string('role', 20)->default('gallery')->after('product_id')->index();
            $table->boolean('is_primary')->default(false)->after('role')->index();
            $table->string('original_name')->nullable()->after('url');
            $table->string('mime_type', 100)->nullable()->after('original_name');
            $table->unsignedBigInteger('size_bytes')->nullable()->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_primary', 'original_name', 'mime_type', 'size_bytes']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['barcode']);
            $table->dropColumn([
                'purchase_price_cents',
                'compare_at_price_cents',
                'price_includes_tax',
                'barcode',
                'brand',
                'supplier_reference',
                'unit_label',
            ]);
        });
    }
};
