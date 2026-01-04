<?php

namespace App\Application\Certification\Handlers;

use App\Application\Certification\Queries\GetCertificationByIdQuery;
use App\Infrastructure\Repositories\Contracts\CertificationRepositoryInterface;

class GetCertificationByIdHandler
{
    public function __construct(
        private CertificationRepositoryInterface $certificationRepository
    ) {}

    public function handle(GetCertificationByIdQuery $query)
    {
        return $this->certificationRepository->findById($query->certificationId);
    }
}

