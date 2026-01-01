<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Get the permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withTimestamps();
    }
}
