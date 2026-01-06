<?php

namespace App\Domain\TravelBook\Models;

use App\Domain\Booking\Models\Booking;
use App\Domain\Experience\Models\Experience;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TravelBookEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'traveler_id',
        'experience_id',
        'booking_id',
        'title',
        'content',
        'entry_date',
        'location',
        'location_details',
        'photos',
        'tags',
        'visibility',
        'is_featured',
        'views_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'entry_date' => 'date',
            'location_details' => 'array',
            'photos' => 'array',
            'tags' => 'array',
            'visibility' => 'string',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($entry) {
            if (empty($entry->uuid)) {
                $entry->uuid = (string) Str::uuid();
            }
        });
    }

    public function traveler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class, 'experience_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}


