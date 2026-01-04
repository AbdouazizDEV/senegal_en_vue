<?php

namespace App\Application\Traveler\Handlers;

use App\Application\Traveler\Commands\UpdateProfileCommand;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class UpdateProfileHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(UpdateProfileCommand $command)
    {
        $user = $this->userRepository->findById($command->userId);
        
        if (!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        return $this->userRepository->update($user, $command->data);
    }
}

