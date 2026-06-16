<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('provider', 64);
            $table->json('display_name');
            $table->json('description')->nullable();
            $table->string('environment', 16)->default('sandbox');
            $table->string('status', 16)->default('draft');
            $table->boolean('is_enabled')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('countries')->nullable();
            $table->json('currencies')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('public_config')->nullable();
            $table->text('credentials')->nullable();
            $table->text('webhook_config')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status', 32)->nullable();
            $table->text('last_test_message')->nullable();
            $table->timestamps();

            $table->index(['provider', 'environment']);
            $table->index(['is_enabled', 'sort_order']);
            $table->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
