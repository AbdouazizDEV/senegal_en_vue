<?php

namespace App\Application\User\Handlers;

use App\Application\User\Commands\DeleteUserCommand;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class DeleteUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(DeleteUserCommand $command): bool
    {
        return $this->userRepository->delete($command->userId);
    }
}


