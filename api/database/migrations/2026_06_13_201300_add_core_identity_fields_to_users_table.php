<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone', 32)->nullable()->after('email_verified_at');
            $table->string('preferred_locale', 2)->default('fr')->after('phone');
            $table->string('country_code', 2)->nullable()->after('preferred_locale');
            $table->string('timezone')->default('Europe/Paris')->after('country_code');
            $table->string('status', 32)->default('active')->after('timezone');
            $table->softDeletes();

            $table->index(['status', 'country_code']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status', 'country_code']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'preferred_locale',
                'country_code',
                'timezone',
                'status',
            ]);
        });
    }
};
