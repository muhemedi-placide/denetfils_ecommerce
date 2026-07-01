<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $customerRoleId = DB::table('roles')->where([
            'name' => 'customer',
            'guard_name' => 'web',
        ])->value('id');

        if (! $customerRoleId) {
            $customerRoleId = DB::table('roles')->insertGetId([
                'name' => 'customer',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('id')
                ->constrained('roles')
                ->restrictOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('id')
                ->constrained('roles')
                ->restrictOnDelete();
        });

        DB::table('customers')->update(['role_id' => $customerRoleId]);

        DB::table('users')->orderBy('id')->each(function ($user): void {
            $roleId = DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->where('model_id', $user->id)
                ->orderBy('role_id')
                ->value('role_id');

            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
