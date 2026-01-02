<?php

namespace App\Domain\Experience\Enums;

enum ReportReason: string
{
    case INAPPROPRIATE = 'inappropriate';
    case FRAUD = 'fraud';
    case SPAM = 'spam';
    case MISLEADING = 'misleading';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::INAPPROPRIATE => 'Contenu inapproprié',
            self::FRAUD => 'Fraude',
            self::SPAM => 'Spam',
            self::MISLEADING => 'Informations trompeuses',
            self::OTHER => 'Autre',
        };
    }
}

