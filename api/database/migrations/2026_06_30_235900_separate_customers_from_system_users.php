<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('preferred_locale', 2)->default('fr');
            $table->string('country_code', 2)->nullable();
            $table->string('timezone')->default('Europe/Paris');
            $table->string('status', 32)->default('active');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('customer_profiles', fn (Blueprint $table) => $table->foreignId('customer_id')->nullable()->after('id')->constrained()->cascadeOnDelete());
        Schema::table('user_addresses', fn (Blueprint $table) => $table->foreignId('customer_id')->nullable()->after('id')->constrained()->cascadeOnDelete());
        Schema::table('privacy_consents', fn (Blueprint $table) => $table->foreignId('customer_id')->nullable()->after('id')->constrained()->nullOnDelete());
        Schema::table('orders', fn (Blueprint $table) => $table->foreignId('customer_id')->nullable()->after('order_number')->constrained()->cascadeOnDelete());
        Schema::table('order_messages', fn (Blueprint $table) => $table->foreignId('customer_id')->nullable()->after('user_id')->constrained()->nullOnDelete());

        $customerIds = collect()
            ->merge(DB::table('customer_profiles')->pluck('user_id'))
            ->merge(DB::table('user_addresses')->pluck('user_id'))
            ->merge(DB::table('privacy_consents')->pluck('user_id'))
            ->merge(DB::table('orders')->pluck('user_id'));

        $customerRoleId = DB::table('roles')->where('name', 'customer')->value('id');

        if ($customerRoleId) {
            $customerIds = $customerIds->merge(
                DB::table('model_has_roles')
                    ->where('role_id', $customerRoleId)
                    ->where('model_type', 'App\\Models\\User')
                    ->pluck('model_id')
            );
        }

        $customerIds = $customerIds->filter()->unique()->values();
        $systemUserIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('roles.name', '!=', 'customer')
            ->pluck('model_has_roles.model_id')
            ->merge(DB::table('staff_profiles')->pluck('user_id'))
            ->filter()
            ->unique()
            ->values();
        $customerOnlyIds = $customerIds->diff($systemUserIds)->values();
        $mixedAccountIds = $customerIds->intersect($systemUserIds)->values();

        if ($customerIds->isNotEmpty()) {
            DB::table('customers')->insert(
                DB::table('users')->whereIn('id', $customerIds)->orderBy('id')->get()->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'first_name' => $user->first_name ?? null,
                    'last_name' => $user->last_name ?? null,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'phone' => $user->phone ?? null,
                    'preferred_locale' => $user->preferred_locale ?? 'fr',
                    'country_code' => $user->country_code ?? null,
                    'timezone' => $user->timezone ?? 'Europe/Paris',
                    'status' => $user->status ?? 'active',
                    'password' => $user->password,
                    'remember_token' => $user->remember_token,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'deleted_at' => $user->deleted_at ?? null,
                ])->all()
            );

            DB::table('customer_profiles')->whereIn('user_id', $customerIds)->update(['customer_id' => DB::raw('user_id')]);
            DB::table('user_addresses')->whereIn('user_id', $customerIds)->update(['customer_id' => DB::raw('user_id')]);
            DB::table('privacy_consents')->whereIn('user_id', $customerIds)->update(['customer_id' => DB::raw('user_id')]);
            DB::table('orders')->whereIn('user_id', $customerIds)->update(['customer_id' => DB::raw('user_id')]);
            DB::table('order_messages')->where('sender_type', 'customer')->whereIn('user_id', $customerIds)->update([
                'customer_id' => DB::raw('user_id'),
                'user_id' => null,
            ]);
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $customerOnlyIds)
                ->update(['tokenable_type' => 'App\\Models\\Customer']);

            if ($mixedAccountIds->isNotEmpty()) {
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', 'App\\Models\\User')
                    ->whereIn('tokenable_id', $mixedAccountIds)
                    ->delete();
            }
        }

        $this->dropLegacyCustomerForeignKeys();

        if ($customerIds->isNotEmpty()) {
            if ($customerRoleId) {
                DB::table('model_has_roles')
                    ->where('role_id', $customerRoleId)
                    ->where('model_type', 'App\\Models\\User')
                    ->whereIn('model_id', $customerIds)
                    ->delete();
            }

            DB::table('model_has_permissions')
                ->where('model_type', 'App\\Models\\User')
                ->whereIn('model_id', $customerOnlyIds)
                ->delete();
            DB::table('users')->whereIn('id', $customerOnlyIds)->delete();
        }
    }

    private function dropLegacyCustomerForeignKeys(): void
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
            $table->unique('customer_id');
        });
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'type']);
            $table->dropColumn('user_id');
            $table->index(['customer_id', 'type']);
        });
        Schema::table('privacy_consents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'type']);
            $table->dropColumn('user_id');
            $table->index(['customer_id', 'type']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropColumn('user_id');
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Customer/system-user separation cannot be safely reversed automatically.');
    }
};
