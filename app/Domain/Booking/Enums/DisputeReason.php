<?php

namespace App\Domain\Booking\Enums;

enum DisputeReason: string
{
    case SERVICE_NOT_PROVIDED = 'service_not_provided';
    case QUALITY_ISSUE = 'quality_issue';
    case CANCELLATION_DISPUTE = 'cancellation_dispute';
    case PAYMENT_ISSUE = 'payment_issue';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::SERVICE_NOT_PROVIDED => 'Service non fourni',
            self::QUALITY_ISSUE => 'Problème de qualité',
            self::CANCELLATION_DISPUTE => 'Litige d\'annulation',
            self::PAYMENT_ISSUE => 'Problème de paiement',
            self::OTHER => 'Autre',
        };
    }
}

