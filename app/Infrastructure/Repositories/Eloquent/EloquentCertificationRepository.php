<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Certification\Enums\CertificationStatus;
use App\Domain\Certification\Models\Certification;
use App\Domain\Certification\Models\ProviderCertification;
use App\Domain\User\Models\User;
use App\Infrastructure\Repositories\Contracts\CertificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EloquentCertificationRepository implements CertificationRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Certification::query();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ?Certification
    {
        return Certification::find($id);
    }

    public function create(array $data): Certification
    {
        if (empty($data['slug']) && isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        return Certification::create($data);
    }

    public function update(Certification $certification, array $data): Certification
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $certification->update($data);
        return $certification->fresh();
    }

    public function delete(Certification $certification): bool
    {
        return $certification->delete();
    }

    public function certifyProvider(User $provider, int $certificationId, array $data): ProviderCertification
    {
        $expiresAt = null;
        if (isset($data['validity_months']) && $data['validity_months']) {
            $expiresAt = now()->addMonths($data['validity_months']);
        }

        return ProviderCertification::updateOrCreate(
            [
                'provider_id' => $provider->id,
                'certification_id' => $certificationId,
            ],
            array_merge($data, [
                'issued_at' => $data['issued_at'] ?? now(),
                'expires_at' => $expiresAt,
                'status' => CertificationStatus::ACTIVE,
                'issued_by' => auth()->id(),
            ])
        );
    }

    public function revokeCertification(User $provider, int $certificationId): bool
    {
        $providerCertification = ProviderCertification::where('provider_id', $provider->id)
            ->where('certification_id', $certificationId)
            ->first();

        if (!$providerCertification) {
            return false;
        }

        $providerCertification->update(['status' => CertificationStatus::REVOKED]);
        return true;
    }

    public function getProviderCertifications(User $provider): \Illuminate\Database\Eloquent\Collection
    {
        return ProviderCertification::with('certification')
            ->where('provider_id', $provider->id)
            ->get();
    }
}

