<?php

namespace App\Application\Certification\Handlers;

use App\Application\Certification\Queries\GetAllCertificationsQuery;
use App\Infrastructure\Repositories\Contracts\CertificationRepositoryInterface;

class GetAllCertificationsHandler
{
    public function __construct(
        private CertificationRepositoryInterface $certificationRepository
    ) {}

    public function handle(GetAllCertificationsQuery $query)
    {
        $filters = array_filter([
            'type' => $query->type,
            'is_active' => $query->isActive,
        ]);

        return $this->certificationRepository->getAll($filters, $query->perPage);
    }
}

