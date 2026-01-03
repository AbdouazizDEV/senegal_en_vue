<?php

namespace App\Domain\Review\Models;

use App\Domain\Booking\Models\Booking;
use App\Domain\Experience\Models\Experience;
use App\Domain\Review\Enums\ReviewStatus;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'booking_id',
        'experience_id',
        'traveler_id',
        'provider_id',
        'rating',
        'title',
        'comment',
        'status',
        'is_verified',
        'is_featured',
        'helpful_count',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'rating' => 'integer',
            'status' => ReviewStatus::class,
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'helpful_count' => 'integer',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'images' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($review) {
            if (empty($review->uuid)) {
                $review->uuid = (string) Str::uuid();
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function traveler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ReviewStatus::APPROVED);
    }

    public function scopeReported($query)
    {
        return $query->where('status', ReviewStatus::REPORTED);
    }
}

