<?php

namespace App\Domain\Discovery\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DiscoveryPreference extends Model
{
    use HasUuids;

    protected $table = 'discovery_preferences';

    protected $fillable = [
        'user_id',
        'preferred_types',
        'preferred_regions',
        'preferred_tags',
        'min_price',
        'max_price',
        'min_duration_minutes',
        'max_duration_minutes',
        'preferred_participants',
        'budget_range',
        'interests',
        'prefer_featured',
        'prefer_eco_friendly',
        'prefer_certified_providers',
    ];

    protected $casts = [
        'preferred_types' => 'array',
        'preferred_regions' => 'array',
        'preferred_tags' => 'array',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'min_duration_minutes' => 'integer',
        'max_duration_minutes' => 'integer',
        'preferred_participants' => 'integer',
        'budget_range' => 'array',
        'interests' => 'array',
        'prefer_featured' => 'boolean',
        'prefer_eco_friendly' => 'boolean',
        'prefer_certified_providers' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($preference) {
            if (empty($preference->uuid)) {
                $preference->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


