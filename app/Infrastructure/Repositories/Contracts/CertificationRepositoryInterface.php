<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Certification\Models\Certification;
use App\Domain\Certification\Models\ProviderCertification;
use App\Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CertificationRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function findById(int $id): ?Certification;
    
    public function create(array $data): Certification;
    
    public function update(Certification $certification, array $data): Certification;
    
    public function delete(Certification $certification): bool;
    
    public function certifyProvider(User $provider, int $certificationId, array $data): ProviderCertification;
    
    public function revokeCertification(User $provider, int $certificationId): bool;
    
    public function getProviderCertifications(User $provider): \Illuminate\Database\Eloquent\Collection;
}

