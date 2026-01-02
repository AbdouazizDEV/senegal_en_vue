<?php

namespace App\Application\Experience\Commands;

use App\Domain\Experience\Enums\ExperienceStatus;

readonly class ModerateExperienceCommand
{
    public function __construct(
        public int $experienceId,
        public ExperienceStatus $status,
        public ?string $reason = null
    ) {}
}

