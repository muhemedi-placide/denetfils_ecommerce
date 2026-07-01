<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\AssignRolesRequest;
use App\Http\Requests\Api\Admin\CreateUserRequest;
use App\Http\Requests\Api\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Core\UserProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with(['roles', 'permissions', 'staffProfile'])
            ->latest('id');

        if ($request->filled('q')) {
            $search = '%' . trim((string) $request->query('q')) . '%';

            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', $search)
                    ->orWhere('first_name', 'like', $search)
                    ->orWhere('last_name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('role')) {
            $query->role((string) $request->query('role'));
        }

        if ($request->filled('country_code')) {
            $query->where('country_code', strtoupper((string) $request->query('country_code')));
        }

        $perPage = max(5, min(100, $request->integer('per_page', 25)));

        return UserResource::collection(
            $query->paginate($perPage),
        );
    }

    public function store(CreateUserRequest $request, UserProvisioningService $users): JsonResponse
    {
        $user = $users->createStaffUser($request->validated(), $request->user(), $request);

        return response()->json([
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load(['roles', 'permissions', 'staffProfile']));
    }

    public function update(UpdateUserRequest $request, User $user, UserProvisioningService $users): UserResource
    {
        return new UserResource($users->updateUser($user, $request->validated(), $request->user(), $request));
    }

    public function assignRoles(AssignRolesRequest $request, User $user, UserProvisioningService $users): UserResource
    {
        return new UserResource($users->assignRoles($user, $request->validated('roles'), $request->user(), $request));
    }

    public function suspend(Request $request, User $user, UserProvisioningService $users): UserResource
    {
        return new UserResource($users->suspend($user, $request->user(), $request));
    }
}
