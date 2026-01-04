<?php

namespace App\Domain\Certification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Certification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'type',
        'badge_image',
        'criteria',
        'validity_months',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'criteria' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($certification) {
            if (empty($certification->uuid)) {
                $certification->uuid = (string) Str::uuid();
            }
            if (empty($certification->slug)) {
                $certification->slug = Str::slug($certification->name);
            }
        });
    }

    public function providerCertifications(): HasMany
    {
        return $this->hasMany(ProviderCertification::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

