<?php

namespace App\Domain\Certification\Enums;

enum CertificationStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::EXPIRED => 'Expirée',
            self::REVOKED => 'Révoquée',
        };
    }
}

