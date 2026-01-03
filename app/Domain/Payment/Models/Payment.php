<?php

namespace App\Domain\Payment\Models;

use App\Domain\Booking\Models\Booking;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'booking_id',
        'traveler_id',
        'provider_id',
        'status',
        'type',
        'amount',
        'commission_amount',
        'provider_amount',
        'currency',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'gateway_reference',
        'gateway_status',
        'gateway_response',
        'processed_at',
        'transferred_at',
        'failure_reason',
        'refund_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'status' => PaymentStatus::class,
            'type' => PaymentType::class,
            'amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'provider_amount' => 'decimal:2',
            'processed_at' => 'datetime',
            'transferred_at' => 'datetime',
            'metadata' => 'array',
            'gateway_response' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->uuid)) {
                $payment->uuid = (string) Str::uuid();
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function traveler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(PaymentDispute::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', PaymentStatus::COMPLETED);
    }

    public function scopeRefunded($query)
    {
        return $query->whereIn('status', [PaymentStatus::REFUNDED, PaymentStatus::PARTIALLY_REFUNDED]);
    }
}

