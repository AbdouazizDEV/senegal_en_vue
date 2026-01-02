<?php

namespace App\Application\Experience\Commands;

readonly class UpdateExperienceCommand
{
    public function __construct(
        public int $experienceId,
        public array $data
    ) {}
}

