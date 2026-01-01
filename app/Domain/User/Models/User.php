<?php

namespace App\Domain\User\Models;

use App\Domain\User\Enums\UserRole;
use App\Domain\User\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'avatar',
        'bio',
        'preferences',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
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
            'uuid' => 'string',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'preferences' => 'array',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'uuid' => $this->uuid,
            'role' => $this->role->value,
            'email' => $this->email,
            'status' => $this->status->value,
        ];
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Get the permissions that belong to the user through roles.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')
            ->wherePivotIn('role_id', $this->roles()->pluck('roles.id'))
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string|UserRole $role): bool
    {
        if ($role instanceof UserRole) {
            return $this->role === $role;
        }

        return $this->role->value === $role;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Admin has all permissions
        if ($this->hasRole(UserRole::ADMIN)) {
            return true;
        }

        // Check role-based permissions
        $rolePermissions = $this->role->permissions();
        if (in_array('*', $rolePermissions) || in_array($permission, $rolePermissions)) {
            return true;
        }

        // Check database permissions
        return $this->permissions()->where('slug', $permission)->exists();
    }

    /**
     * Check if user can login.
     */
    public function canLogin(): bool
    {
        return $this->status->canLogin();
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::ACTIVE->value);
    }

    /**
     * Scope a query to filter by role.
     */
    public function scopeRole($query, UserRole $role)
    {
        return $query->where('role', $role->value);
    }
}
