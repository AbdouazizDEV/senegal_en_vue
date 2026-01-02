<?php

namespace App\Application\Experience\Queries;

readonly class GetExperienceByIdQuery
{
    public function __construct(
        public int $experienceId
    ) {}
}

