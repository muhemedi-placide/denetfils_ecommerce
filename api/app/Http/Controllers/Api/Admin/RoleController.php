<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Role::query()
                ->with('permissions')
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->values(),
                ])
                ->values(),
        ]);
    }
}
