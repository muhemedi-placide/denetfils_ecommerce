<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_carrier_id')->constrained()->cascadeOnDelete();
            $table->string('code', 96)->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('delivery_type', 24)->index();
            $table->string('service_code', 16);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('requires_pickup_point')->default(false);
            $table->boolean('requires_phone')->default(false);
            $table->unsignedInteger('max_weight_grams')->nullable();
            $table->unsignedSmallInteger('min_delivery_days')->nullable();
            $table->unsignedSmallInteger('max_delivery_days')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('configuration')->nullable();
            $table->timestamps();
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('countries');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_weight_grams')->default(0);
            $table->unsignedInteger('max_weight_grams');
            $table->unsignedInteger('price_cents');
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['shipping_method_id', 'shipping_zone_id', 'min_weight_grams', 'max_weight_grams'], 'shipping_rate_lookup');
        });

        Schema::create('pickup_points', function (Blueprint $table) {
            $table->id();
            $table->string('carrier_code', 64);
            $table->string('external_id', 96);
            $table->string('type', 24)->default('pickup_point');
            $table->string('country', 2);
            $table->string('name');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('postal_code', 32);
            $table->string('city');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('opening_hours')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->unique(['carrier_code', 'external_id']);
        });

        Schema::create('cart_shipping_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_method_id')->constrained()->restrictOnDelete();
            $table->foreignId('pickup_point_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('shipping_price_cents');
            $table->string('currency', 3)->default('EUR');
            $table->string('country', 2);
            $table->string('postal_code', 32)->nullable();
            $table->string('city')->nullable();
            $table->json('address_snapshot')->nullable();
            $table->timestamps();
        });

        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_carrier_id')->constrained()->restrictOnDelete();
            $table->foreignId('shipping_method_id')->constrained()->restrictOnDelete();
            $table->foreignId('pickup_point_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('tracking_number', 120)->nullable()->index();
            $table->string('label_path')->nullable();
            $table->string('external_shipment_id', 120)->nullable()->index();
            $table->string('status', 32)->default('pending')->index();
            $table->text('last_error')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
        Schema::dropIfExists('cart_shipping_selections');
        Schema::dropIfExists('pickup_points');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('shipping_methods');
    }
};
