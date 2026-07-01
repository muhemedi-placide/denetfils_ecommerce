<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\SyncRolePermissionsRequest;
use App\Services\Core\AuditLogger;
use Illuminate\Http\JsonResponse;
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

    public function syncPermissions(
        SyncRolePermissionsRequest $request,
        Role $role,
        AuditLogger $auditLogger,
    ): JsonResponse {
        abort_if(
            in_array($role->name, ['super_admin', 'admin', 'customer'], true),
            422,
            'Permissions for this protected role cannot be changed.',
        );

        $permissions = $request->validated('permissions', []);
        $role->syncPermissions($permissions);

        $auditLogger->record($request->user(), 'roles.permissions_updated', $role, $request, [
            'permissions' => $permissions,
        ]);

        return response()->json([
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions()->orderBy('name')->pluck('name')->values(),
            ],
        ]);
    }
}
