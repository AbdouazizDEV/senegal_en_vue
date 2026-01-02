<?php

namespace App\Application\Experience\Commands;

readonly class DeleteExperienceCommand
{
    public function __construct(
        public int $experienceId
    ) {}
}

