<?php

namespace App\Domain\Review\Enums;

enum ReportReason: string
{
    case INAPPROPRIATE = 'inappropriate';
    case SPAM = 'spam';
    case FAKE = 'fake';
    case OFFENSIVE = 'offensive';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::INAPPROPRIATE => 'Contenu inapproprié',
            self::SPAM => 'Spam',
            self::FAKE => 'Avis faux',
            self::OFFENSIVE => 'Contenu offensant',
            self::OTHER => 'Autre',
        };
    }
}

