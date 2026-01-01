<?php

namespace App\Application\User\Handlers;

use App\Application\User\Commands\RegisterTravelerCommand;
use App\Domain\User\Enums\UserRole;
use App\Domain\User\Enums\UserStatus;
use App\Domain\User\Models\User;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class RegisterTravelerHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(RegisterTravelerCommand $command): User
    {
        $userData = [
            'name' => $command->name,
            'email' => $command->email,
            'phone' => $command->phone,
            'password' => Hash::make($command->password),
            'role' => UserRole::TRAVELER,
            'status' => UserStatus::PENDING_VERIFICATION,
            'preferences' => $command->preferences,
        ];

        return $this->userRepository->create($userData);
    }
}

