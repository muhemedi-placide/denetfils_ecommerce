<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cart_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('status', 32)->default('pending_payment');
            $table->string('payment_status', 32)->default('unpaid');
            $table->string('fulfillment_status', 32)->default('unfulfilled');
            $table->string('currency', 3)->default('EUR');
            $table->unsignedInteger('subtotal_cents')->default(0);
            $table->unsignedInteger('tax_cents')->default(0);
            $table->unsignedInteger('shipping_cents')->default(0);
            $table->unsignedInteger('discount_cents')->default(0);
            $table->unsignedInteger('total_cents')->default(0);
            $table->string('customer_email');
            $table->string('customer_name');
            $table->string('customer_phone', 32)->nullable();
            $table->string('customer_locale', 2)->default('fr');
            $table->string('customer_country_code', 2)->nullable();
            $table->string('delivery_method', 32)->nullable();
            $table->string('carrier', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['payment_status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('product_name');
            $table->string('product_slug')->nullable();
            $table->string('product_sku')->nullable();
            $table->json('variant_name')->nullable();
            $table->string('variant_sku')->nullable();
            $table->string('category_slug')->nullable();
            $table->json('category_name')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->json('image_alt_text')->nullable();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('line_total_cents');
            $table->string('currency', 3)->default('EUR');
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_address_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 16);
            $table->string('label')->nullable();
            $table->string('recipient_name');
            $table->string('company')->nullable();
            $table->string('street_line_1');
            $table->string('street_line_2')->nullable();
            $table->string('postal_code', 32);
            $table->string('city');
            $table->string('region')->nullable();
            $table->string('country_code', 2);
            $table->string('phone', 32)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
