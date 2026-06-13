<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Me\AddressRequest;
use App\Http\Resources\UserAddressResource;
use App\Models\UserAddress;
use App\Services\Core\AddressBookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return UserAddressResource::collection(
            $request->user()->addresses()->orderByDesc('is_default')->orderByDesc('id')->get(),
        );
    }

    public function store(AddressRequest $request, AddressBookService $addresses): JsonResponse
    {
        $address = $addresses->create($request->user(), $request->validated());

        return response()->json([
            'data' => new UserAddressResource($address),
        ], 201);
    }

    public function update(AddressRequest $request, UserAddress $address, AddressBookService $addresses): UserAddressResource
    {
        abort_unless($address->user_id === $request->user()->id, 404);

        return new UserAddressResource($addresses->update($address, $request->validated()));
    }

    public function destroy(Request $request, UserAddress $address): JsonResponse
    {
        abort_unless($address->user_id === $request->user()->id, 404);

        $address->delete();

        return response()->json([
            'message' => 'Address deleted.',
        ]);
    }
}
