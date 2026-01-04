<?php

namespace App\Application\Certification\Handlers;

use App\Application\Certification\Commands\UpdateCertificationCommand;
use App\Infrastructure\Repositories\Contracts\CertificationRepositoryInterface;

class UpdateCertificationHandler
{
    public function __construct(
        private CertificationRepositoryInterface $certificationRepository
    ) {}

    public function handle(UpdateCertificationCommand $command)
    {
        $certification = $this->certificationRepository->findById($command->certificationId);
        
        if (!$certification) {
            throw new \RuntimeException('Certification non trouvée');
        }

        return $this->certificationRepository->update($certification, $command->data);
    }
}

