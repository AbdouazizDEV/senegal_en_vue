<?php

namespace App\Application\Heritage\Queries;

class GetHeritageStoryQuery
{
    public function __construct(
        public readonly int|string $storyId // Peut être ID ou UUID
    ) {}
}


