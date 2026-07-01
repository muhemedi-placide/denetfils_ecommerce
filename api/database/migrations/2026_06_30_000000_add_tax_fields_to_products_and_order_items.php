<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('tax_class', 24)->default('food')->after('currency')->index();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('tax_class', 24)->default('food')->after('currency');
            $table->decimal('tax_rate_percent', 5, 2)->default(0)->after('tax_class');
            $table->unsignedInteger('tax_cents')->default(0)->after('tax_rate_percent');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['tax_class', 'tax_rate_percent', 'tax_cents']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['tax_class']);
            $table->dropColumn('tax_class');
        });
    }
};
