<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles {
        assignRole as protected assignSpatieRole;
        syncRoles as protected syncSpatieRoles;
    }
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'role_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'preferred_locale',
        'country_code',
        'timezone',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function assignRole(...$roles)
    {
        $result = $this->assignSpatieRole(...$roles);
        $this->syncPrimaryRoleColumn();

        return $result;
    }

    public function syncRoles(...$roles)
    {
        $result = $this->syncSpatieRoles(...$roles);
        $this->syncPrimaryRoleColumn();

        return $result;
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    public function orderMessages(): HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    private function syncPrimaryRoleColumn(): void
    {
        $roleId = $this->roles()->orderBy('roles.id')->value('roles.id');

        if ($this->role_id !== $roleId) {
            $this->forceFill(['role_id' => $roleId])->saveQuietly();
            $this->unsetRelation('role');
        }
    }
}
