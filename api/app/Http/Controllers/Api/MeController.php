<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Me\UpdateMeRequest;
use App\Http\Resources\CustomerResource;
use App\Services\Core\UserProvisioningService;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): CustomerResource
    {
        return new CustomerResource($request->user()->load('customerProfile'));
    }

    public function update(UpdateMeRequest $request, UserProvisioningService $users): CustomerResource
    {
        return new CustomerResource($users->updateProfile($request->user(), $request->validated()));
    }
}
