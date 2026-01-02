<?php

namespace App\Domain\Experience\Enums;

enum ExperienceType: string
{
    case ACTIVITY = 'activity';
    case TOUR = 'tour';
    case WORKSHOP = 'workshop';
    case EVENT = 'event';
    case ACCOMMODATION = 'accommodation';
    case RESTAURANT = 'restaurant';

    public function label(): string
    {
        return match($this) {
            self::ACTIVITY => 'Activité',
            self::TOUR => 'Visite guidée',
            self::WORKSHOP => 'Atelier',
            self::EVENT => 'Événement',
            self::ACCOMMODATION => 'Hébergement',
            self::RESTAURANT => 'Restaurant',
        };
    }
}

