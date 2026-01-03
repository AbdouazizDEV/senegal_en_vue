<?php

namespace App\Domain\Payment\Enums;

enum PaymentType: string
{
    case BOOKING = 'booking';
    case REFUND = 'refund';
    case COMMISSION = 'commission';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match($this) {
            self::BOOKING => 'Paiement de réservation',
            self::REFUND => 'Remboursement',
            self::COMMISSION => 'Commission',
            self::TRANSFER => 'Transfert',
        };
    }
}

