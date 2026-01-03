<?php

namespace App\Domain\Booking\Models;

use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Enums\PaymentStatus;
use App\Domain\Experience\Models\Experience;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'experience_id',
        'traveler_id',
        'provider_id',
        'status',
        'booking_date',
        'booking_time',
        'participants_count',
        'total_amount',
        'currency',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_date',
        'special_requests',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'confirmed_at',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'status' => BookingStatus::class,
            'payment_status' => PaymentStatus::class,
            'booking_date' => 'date',
            'booking_time' => 'datetime:H:i',
            'participants_count' => 'integer',
            'total_amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'cancelled_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->uuid)) {
                $booking->uuid = (string) Str::uuid();
            }
        });
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

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(BookingDispute::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', BookingStatus::PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', BookingStatus::CONFIRMED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', BookingStatus::CANCELLED);
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', BookingStatus::DISPUTED);
    }

    public function scopeByStatus($query, BookingStatus $status)
    {
        return $query->where('status', $status);
    }
}

