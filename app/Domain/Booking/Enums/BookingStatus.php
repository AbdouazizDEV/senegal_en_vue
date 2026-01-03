<?php

namespace App\Domain\Booking\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case DISPUTED = 'disputed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmée',
            self::CANCELLED => 'Annulée',
            self::COMPLETED => 'Terminée',
            self::DISPUTED => 'En litige',
            self::REFUNDED => 'Remboursée',
        };
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }
}

