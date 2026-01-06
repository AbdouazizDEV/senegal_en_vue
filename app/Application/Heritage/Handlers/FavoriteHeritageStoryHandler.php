<?php

namespace App\Application\Heritage\Handlers;

use App\Application\Heritage\Commands\FavoriteHeritageStoryCommand;
use App\Domain\Favorite\Models\Favorite;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;

class FavoriteHeritageStoryHandler
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository
    ) {}

    public function handle(FavoriteHeritageStoryCommand $command): array
    {
        $story = $this->contentRepository->findHeritageStoryById($command->storyId);

        if (!$story) {
            throw new \Exception('Histoire non trouvée.');
        }

        // Pour l'instant, on retourne simplement une confirmation
        // TODO: Créer une table heritage_story_favorites si nécessaire
        // ou utiliser une table de favoris générique avec un type polymorphique
        
        return [
            'user_id' => $command->userId,
            'heritage_story_id' => $command->storyId,
            'message' => 'Histoire ajoutée aux favoris',
        ];
    }
}

