<?php

namespace App\Domain\User\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case TRAVELER = 'traveler';
    case PROVIDER = 'provider';
    case INSTITUTION = 'institution';

    public function permissions(): array
    {
        return match($this) {
            self::ADMIN => ['*'],
            self::TRAVELER => [
                'bookings.create',
                'bookings.view',
                'bookings.cancel',
                'reviews.create',
                'reviews.view',
                'messages.send',
                'messages.view',
            ],
            self::PROVIDER => [
                'experiences.create',
                'experiences.update',
                'experiences.delete',
                'bookings.manage',
                'bookings.view',
                'calendar.manage',
                'statistics.view',
                'messages.send',
                'messages.view',
            ],
            self::INSTITUTION => [
                'analytics.view',
                'reports.view',
            ],
        };
    }

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrateur',
            self::TRAVELER => 'Voyageur',
            self::PROVIDER => 'Prestataire',
            self::INSTITUTION => 'Institution',
        };
    }
}

