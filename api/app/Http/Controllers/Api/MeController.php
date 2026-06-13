<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Me\UpdateMeRequest;
use App\Http\Resources\UserResource;
use App\Services\Core\UserProvisioningService;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['roles', 'permissions', 'customerProfile', 'staffProfile']));
    }

    public function update(UpdateMeRequest $request, UserProvisioningService $users): UserResource
    {
        return new UserResource($users->updateProfile($request->user(), $request->validated()));
    }
}
