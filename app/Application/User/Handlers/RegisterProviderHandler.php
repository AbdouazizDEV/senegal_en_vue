<?php

namespace App\Application\User\Handlers;

use App\Application\User\Commands\RegisterProviderCommand;
use App\Domain\User\Enums\UserRole;
use App\Domain\User\Enums\UserStatus;
use App\Domain\User\Models\User;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class RegisterProviderHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(RegisterProviderCommand $command): User
    {
        $userData = [
            'name' => $command->name,
            'email' => $command->email,
            'phone' => $command->phone,
            'password' => Hash::make($command->password),
            'role' => UserRole::PROVIDER,
            'status' => UserStatus::PENDING_VERIFICATION,
            'bio' => $command->bio,
            'preferences' => $command->preferences,
        ];

        // TODO: Créer une table provider_details pour stocker les infos business
        // Pour l'instant, on stocke dans preferences
        $userData['preferences'] = array_merge($userData['preferences'] ?? [], [
            'business_name' => $command->businessName,
            'business_registration_number' => $command->businessRegistrationNumber,
            'address' => $command->address,
            'city' => $command->city,
            'region' => $command->region,
        ]);

        return $this->userRepository->create($userData);
    }
}

