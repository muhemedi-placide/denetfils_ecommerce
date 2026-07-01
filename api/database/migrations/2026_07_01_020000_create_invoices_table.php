<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->string('invoice_number', 48)->unique();
            $table->string('status', 24)->default('draft');
            $table->string('currency', 3);
            $table->unsignedInteger('total_cents');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'issued_at']);
        });

        DB::table('orders')->orderBy('id')->each(function ($order): void {
            $status = match (true) {
                $order->payment_status === 'refunded' || $order->status === 'refunded' => 'refunded',
                $order->status === 'cancelled' => 'void',
                $order->payment_status === 'paid' => 'paid',
                in_array($order->status, ['confirmed', 'processing', 'completed'], true) => 'issued',
                default => 'draft',
            };
            $issuedAt = $status === 'draft' ? null : ($order->placed_at ?? $order->created_at);

            DB::table('invoices')->insert([
                'order_id' => $order->id,
                'invoice_number' => 'FAC-'.$order->order_number,
                'status' => $status,
                'currency' => $order->currency,
                'total_cents' => $order->total_cents,
                'issued_at' => $issuedAt,
                'due_at' => $issuedAt ? \Illuminate\Support\Carbon::parse($issuedAt)->addDays(30) : null,
                'paid_at' => $status === 'paid' ? ($order->updated_at ?? $issuedAt) : null,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
