<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 32);
            $table->string('provider_reference', 128)->nullable();
            $table->string('status', 64)->default('pending');
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3);
            $table->string('client_secret', 255)->nullable();
            $table->string('approval_url', 2048)->nullable();
            $table->json('provider_payload')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'provider']);
            $table->index(['provider', 'status']);
            $table->unique(['provider', 'provider_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
