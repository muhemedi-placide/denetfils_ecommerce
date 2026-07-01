<?php

namespace App\Services\Core;

use App\Models\Customer;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;

class AddressBookService
{
    public function create(Customer $user, array $data): UserAddress
    {
        return DB::transaction(function () use ($user, $data) {
            $this->clearDefaultIfNeeded($user, $data);

            if (! $user->addresses()->where('type', $data['type'])->exists()) {
                $data['is_default'] = true;
            }

            return $user->addresses()->create($data);
        });
    }

    public function update(UserAddress $address, array $data): UserAddress
    {
        return DB::transaction(function () use ($address, $data) {
            $this->clearDefaultIfNeeded($address->customer, $data, $address);
            $address->update($data);

            return $address->refresh();
        });
    }

    private function clearDefaultIfNeeded(Customer $user, array $data, ?UserAddress $currentAddress = null): void
    {
        if (! ($data['is_default'] ?? false)) {
            return;
        }

        $query = $user->addresses()
            ->where('type', $data['type'] ?? $currentAddress?->type)
            ->where('is_default', true);

        if ($currentAddress) {
            $query->whereKeyNot($currentAddress->id);
        }

        $query->update(['is_default' => false]);
    }
}
