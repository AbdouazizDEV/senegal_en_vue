<?php

namespace App\Application\User\Handlers;

use App\Application\User\Commands\ActivateUserCommand;
use App\Domain\User\Enums\UserStatus;
use App\Domain\User\Models\User;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class ActivateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(ActivateUserCommand $command): User
    {
        $user = $this->userRepository->findById($command->userId);
        
        if (!$user) {
            throw new \RuntimeException("User with ID {$command->userId} not found");
        }
        
        return $this->userRepository->update($command->userId, [
            'status' => UserStatus::ACTIVE,
        ]);
    }
}


