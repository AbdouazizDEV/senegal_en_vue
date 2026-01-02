<?php

namespace App\Application\User\Handlers;

use App\Application\User\Commands\ValidateProviderCommand;
use App\Domain\User\Enums\UserRole;
use App\Domain\User\Enums\UserStatus;
use App\Domain\User\Models\User;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class ValidateProviderHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(ValidateProviderCommand $command): User
    {
        $user = $this->userRepository->findById($command->userId);
        
        if (!$user) {
            throw new \RuntimeException("User with ID {$command->userId} not found");
        }
        
        if ($user->role !== UserRole::PROVIDER) {
            throw new \RuntimeException("User is not a provider");
        }
        
        return $this->userRepository->update($command->userId, [
            'status' => UserStatus::VERIFIED,
        ]);
    }
}


