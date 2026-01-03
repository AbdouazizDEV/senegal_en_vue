<?php

namespace App\Domain\Booking\Models;

use App\Domain\Booking\Enums\DisputeReason;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'initiated_by',
        'reason',
        'description',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'resolution_type',
        'refund_amount',
        'evidence',
    ];

    protected function casts(): array
    {
        return [
            'reason' => DisputeReason::class,
            'resolved_at' => 'datetime',
            'refund_amount' => 'decimal:2',
            'evidence' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'in_review');
    }
}

