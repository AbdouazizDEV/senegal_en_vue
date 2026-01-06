<?php

namespace App\Application\Heritage\Commands;

class FavoriteHeritageStoryCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $storyId
    ) {}
}


