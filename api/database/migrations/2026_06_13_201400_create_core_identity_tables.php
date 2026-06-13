<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supported_countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->json('name');
            $table->string('currency', 3)->default('EUR');
            $table->string('default_locale', 2)->default('fr');
            $table->string('timezone')->default('Europe/Paris');
            $table->decimal('standard_vat_rate_percent', 5, 2)->default(20);
            $table->decimal('food_vat_rate_percent', 5, 2)->nullable();
            $table->boolean('is_eu')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('accepts_marketing')->default(false);
            $table->timestamp('marketing_consented_at')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('position')->nullable();
            $table->string('operational_status', 32)->default('active');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
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
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['country_code', 'postal_code']);
        });

        Schema::create('privacy_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 64);
            $table->string('version', 32);
            $table->boolean('accepted')->default(false);
            $table->string('locale', 2)->default('fr');
            $table->string('country_code', 2)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['type', 'version']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 96);
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('privacy_consents');
        Schema::dropIfExists('user_addresses');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('supported_countries');
    }
};
