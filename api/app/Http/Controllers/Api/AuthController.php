<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\WelcomeCustomerNotification;
use App\Services\Core\UserProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, UserProvisioningService $users): JsonResponse
    {
        $user = $users->registerCustomer($request->validated(), $request);
        $token = $user->createToken('customer-api')->plainTextToken;
        $user->notify(new WelcomeCustomerNotification($user->preferred_locale ?: 'fr'));

        return response()->json([
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (! $user->isActive()) {
            return response()->json([
                'message' => 'This account is not active.',
            ], 403);
        }

        $token = $user->createToken($request->validated('device_name') ?: 'api')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user->load(['roles', 'permissions', 'customerProfile', 'staffProfile'])),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['roles', 'permissions', 'customerProfile', 'staffProfile']));
    }
}
