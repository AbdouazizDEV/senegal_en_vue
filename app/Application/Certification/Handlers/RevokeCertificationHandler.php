<?php

namespace App\Application\Certification\Handlers;

use App\Application\Certification\Commands\RevokeCertificationCommand;
use App\Infrastructure\Repositories\Contracts\CertificationRepositoryInterface;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class RevokeCertificationHandler
{
    public function __construct(
        private CertificationRepositoryInterface $certificationRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(RevokeCertificationCommand $command): bool
    {
        $provider = $this->userRepository->findById($command->providerId);
        
        if (!$provider) {
            return false;
        }

        return $this->certificationRepository->revokeCertification($provider, $command->certificationId);
    }
}

