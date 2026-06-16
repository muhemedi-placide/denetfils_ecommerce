<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('provider', 64)->index();
            $table->json('display_name');
            $table->json('description')->nullable();
            $table->string('environment', 24)->default('sandbox')->index();
            $table->string('status', 24)->default('draft')->index();
            $table->boolean('is_enabled')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('delivery_modes')->nullable();
            $table->json('countries')->nullable();
            $table->unsignedInteger('max_weight_grams')->nullable();
            $table->boolean('supports_relay_points')->default(true);
            $table->boolean('supports_home_delivery')->default(false);
            $table->json('public_config')->nullable();
            $table->text('credentials')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status', 64)->nullable();
            $table->string('last_test_message', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_carriers');
    }
};
