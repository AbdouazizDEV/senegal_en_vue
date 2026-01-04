<?php

namespace App\Application\Certification\Handlers;

use App\Application\Certification\Commands\CertifyProviderCommand;
use App\Domain\User\Models\User;
use App\Infrastructure\Repositories\Contracts\CertificationRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class CertifyProviderHandler
{
    public function __construct(
        private CertificationRepositoryInterface $certificationRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(CertifyProviderCommand $command)
    {
        $provider = $this->userRepository->findById($command->providerId);
        
        if (!$provider || !$provider->hasRole(\App\Domain\User\Enums\UserRole::PROVIDER)) {
            throw new \RuntimeException('Prestataire non trouvé');
        }

        return $this->certificationRepository->certifyProvider($provider, $command->certificationId, $command->data);
    }
}

