<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Experience\Enums\ExperienceStatus;
use App\Domain\Experience\Models\Experience;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExperienceRepositoryInterface
{
    public function findById(int $id): ?Experience;
    
    public function findByUuid(string $uuid): ?Experience;
    
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function getPending(int $perPage = 15): LengthAwarePaginator;
    
    public function getReported(int $perPage = 15): LengthAwarePaginator;
    
    public function update(Experience $experience, array $data): Experience;
    
    public function delete(Experience $experience): bool;
    
    public function moderate(Experience $experience, ExperienceStatus $status, ?string $reason = null): Experience;
    
    public function getReports(int $perPage = 15): LengthAwarePaginator;
}

