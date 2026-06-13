<?php

namespace App\Services\Core;

use App\Models\PrivacyConsent;
use App\Models\User;
use App\Support\CoreDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserProvisioningService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function registerCustomer(array $data, Request $request): User
    {
        return DB::transaction(function () use ($data, $request) {
            $user = User::create($this->userPayload($data) + [
                'password' => $data['password'],
                'status' => 'active',
            ]);

            $user->customerProfile()->create([
                'accepts_marketing' => (bool) ($data['marketing_consent'] ?? false),
                'marketing_consented_at' => ($data['marketing_consent'] ?? false) ? now() : null,
                'preferences' => [
                    'preferred_locale' => $user->preferred_locale,
                    'country_code' => $user->country_code,
                ],
            ]);

            $user->assignRole('customer');

            $this->recordConsent($user, 'privacy_policy', true, $request);
            $this->recordConsent($user, 'terms', true, $request);
            $this->recordConsent($user, 'marketing_email', (bool) ($data['marketing_consent'] ?? false), $request);

            return $user->load(['roles', 'customerProfile']);
        });
    }

    public function createStaffUser(array $data, User $actor, Request $request): User
    {
        return DB::transaction(function () use ($data, $actor, $request) {
            $user = User::create($this->userPayload($data) + [
                'password' => $data['password'],
                'status' => $data['status'] ?? 'invited',
            ]);

            $roles = $data['roles'] ?? [];

            if (in_array('customer', $roles, true)) {
                $user->customerProfile()->create([
                    'accepts_marketing' => false,
                    'preferences' => null,
                ]);
            } else {
                $user->staffProfile()->create([
                    'position' => $data['position'] ?? null,
                    'operational_status' => 'active',
                    'admin_notes' => $data['admin_notes'] ?? null,
                ]);
            }

            $user->syncRoles($roles ?: ['support_agent']);

            $this->auditLogger->record($actor, 'users.created', $user, $request, [
                'roles' => $user->roles()->pluck('name')->values()->all(),
            ]);

            return $user->load(['roles', 'permissions', 'customerProfile', 'staffProfile']);
        });
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->fill($this->userUpdatePayload($user, $data, includeEmail: false));
        $user->save();

        return $user->refresh()->load(['roles', 'permissions', 'customerProfile', 'staffProfile']);
    }

    public function updateUser(User $user, array $data, User $actor, Request $request): User
    {
        return DB::transaction(function () use ($user, $data, $actor, $request) {
            $payload = $this->userUpdatePayload($user, $data, includeEmail: array_key_exists('email', $data));

            if (isset($data['status'])) {
                $payload['status'] = $data['status'];
            }

            if (isset($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $user->update($payload);

            if ($user->staffProfile && (array_key_exists('position', $data) || array_key_exists('admin_notes', $data))) {
                $user->staffProfile->update([
                    'position' => $data['position'] ?? $user->staffProfile->position,
                    'admin_notes' => $data['admin_notes'] ?? $user->staffProfile->admin_notes,
                ]);
            }

            $this->auditLogger->record($actor, 'users.updated', $user, $request, [
                'fields' => array_keys($data),
            ]);

            return $user->refresh()->load(['roles', 'permissions', 'customerProfile', 'staffProfile']);
        });
    }

    public function assignRoles(User $user, array $roles, User $actor, Request $request): User
    {
        $user->syncRoles($roles);

        $this->auditLogger->record($actor, 'users.roles_assigned', $user, $request, [
            'roles' => $roles,
        ]);

        return $user->refresh()->load(['roles', 'permissions', 'customerProfile', 'staffProfile']);
    }

    public function suspend(User $user, User $actor, Request $request): User
    {
        $user->forceFill(['status' => 'suspended'])->save();
        $user->tokens()->delete();

        $this->auditLogger->record($actor, 'users.suspended', $user, $request);

        return $user->refresh()->load(['roles', 'permissions', 'customerProfile', 'staffProfile']);
    }

    private function recordConsent(User $user, string $type, bool $accepted, Request $request): void
    {
        PrivacyConsent::create([
            'user_id' => $user->id,
            'type' => $type,
            'version' => CoreDefaults::CONSENT_VERSIONS[$type],
            'accepted' => $accepted,
            'locale' => $user->preferred_locale,
            'country_code' => $user->country_code,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'consented_at' => now(),
        ]);
    }

    private function userPayload(array $data, bool $includeEmail = true): array
    {
        $firstName = $data['first_name'] ?? null;
        $lastName = $data['last_name'] ?? null;

        $payload = [
            'name' => trim("{$firstName} {$lastName}") ?: ($data['name'] ?? ''),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $data['phone'] ?? null,
            'preferred_locale' => $data['preferred_locale'] ?? 'fr',
            'country_code' => $data['country_code'] ?? 'FR',
            'timezone' => $data['timezone'] ?? CoreDefaults::DEFAULT_TIMEZONE,
        ];

        if ($includeEmail && isset($data['email'])) {
            $payload['email'] = $data['email'];
        }

        return $payload;
    }

    private function userUpdatePayload(User $user, array $data, bool $includeEmail = true): array
    {
        $firstName = $data['first_name'] ?? $user->first_name;
        $lastName = $data['last_name'] ?? $user->last_name;

        $payload = [
            'name' => trim("{$firstName} {$lastName}") ?: $user->name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => array_key_exists('phone', $data) ? $data['phone'] : $user->phone,
            'preferred_locale' => $data['preferred_locale'] ?? $user->preferred_locale,
            'country_code' => $data['country_code'] ?? $user->country_code,
            'timezone' => $data['timezone'] ?? $user->timezone,
        ];

        if ($includeEmail && isset($data['email'])) {
            $payload['email'] = $data['email'];
        }

        return $payload;
    }
}
