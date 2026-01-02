<?php

namespace App\Domain\Experience\Enums;

enum ExperienceStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';
    case REPORTED = 'reported';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Brouillon',
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            self::SUSPENDED => 'Suspendu',
            self::REPORTED => 'Signalé',
        };
    }

    public function canBePublished(): bool
    {
        return $this === self::APPROVED;
    }

    public function requiresModeration(): bool
    {
        return in_array($this, [self::PENDING, self::REPORTED]);
    }
}

