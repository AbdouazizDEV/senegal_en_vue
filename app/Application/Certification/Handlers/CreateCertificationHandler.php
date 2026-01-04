<?php

namespace App\Application\Certification\Handlers;

use App\Application\Certification\Commands\CreateCertificationCommand;
use App\Infrastructure\Repositories\Contracts\CertificationRepositoryInterface;

class CreateCertificationHandler
{
    public function __construct(
        private CertificationRepositoryInterface $certificationRepository
    ) {}

    public function handle(CreateCertificationCommand $command)
    {
        return $this->certificationRepository->create($command->data);
    }
}

