<?php

namespace App\Domain\User\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING_VERIFICATION = 'pending_verification';
    case VERIFIED = 'verified';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Actif',
            self::INACTIVE => 'Inactif',
            self::SUSPENDED => 'Suspendu',
            self::PENDING_VERIFICATION => 'En attente de vérification',
            self::VERIFIED => 'Vérifié',
        };
    }

    public function canLogin(): bool
    {
        return match($this) {
            self::ACTIVE, self::VERIFIED => true,
            default => false,
        };
    }
}

