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
    public function index()
    {
        return UserResource::collection(
            User::query()
                ->with(['roles', 'permissions', 'customerProfile', 'staffProfile'])
                ->latest('id')
                ->paginate(25),
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
        return new UserResource($user->load(['roles', 'permissions', 'customerProfile', 'staffProfile']));
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
