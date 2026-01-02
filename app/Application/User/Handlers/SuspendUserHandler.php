<?php

namespace App\Application\User\Handlers;

use App\Application\User\Commands\SuspendUserCommand;
use App\Domain\User\Enums\UserStatus;
use App\Domain\User\Models\User;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class SuspendUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(SuspendUserCommand $command): User
    {
        $user = $this->userRepository->findById($command->userId);
        
        if (!$user) {
            throw new \RuntimeException("User with ID {$command->userId} not found");
        }
        
        $updateData = ['status' => UserStatus::SUSPENDED];
        
        if ($command->reason) {
            $preferences = $user->preferences ?? [];
            $preferences['suspension_reason'] = $command->reason;
            $updateData['preferences'] = $preferences;
        }
        
        return $this->userRepository->update($command->userId, $updateData);
    }
}


