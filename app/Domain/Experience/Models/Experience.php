<?php

namespace App\Domain\Experience\Models;

use App\Domain\Experience\Enums\ExperienceStatus;
use App\Domain\Experience\Enums\ExperienceType;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Experience extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'provider_id',
        'title',
        'description',
        'short_description',
        'slug',
        'type',
        'status',
        'price',
        'currency',
        'duration_minutes',
        'max_participants',
        'min_participants',
        'images',
        'location',
        'schedule',
        'tags',
        'amenities',
        'is_featured',
        'views_count',
        'bookings_count',
        'rating',
        'reviews_count',
        'rejection_reason',
        'published_at',
        'approved_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'type' => ExperienceType::class,
            'status' => ExperienceStatus::class,
            'price' => 'decimal:2',
            'images' => 'array',
            'location' => 'array',
            'schedule' => 'array',
            'tags' => 'array',
            'amenities' => 'array',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'bookings_count' => 'integer',
            'rating' => 'decimal:2',
            'reviews_count' => 'integer',
            'published_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($experience) {
            if (empty($experience->uuid)) {
                $experience->uuid = (string) Str::uuid();
            }
            if (empty($experience->slug)) {
                $experience->slug = Str::slug($experience->title);
            }
        });

        static::updating(function ($experience) {
            if ($experience->isDirty('title') && empty($experience->getOriginal('slug'))) {
                $experience->slug = Str::slug($experience->title);
            }
        });
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ExperienceReport::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', ExperienceStatus::PENDING);
    }

    public function scopeReported($query)
    {
        return $query->where('status', ExperienceStatus::REPORTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ExperienceStatus::APPROVED);
    }

    public function scopeByStatus($query, ExperienceStatus $status)
    {
        return $query->where('status', $status);
    }
}

