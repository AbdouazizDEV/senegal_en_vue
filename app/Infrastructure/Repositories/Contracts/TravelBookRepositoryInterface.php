<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\TravelBook\Models\TravelBookEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TravelBookRepositoryInterface
{
    public function findByTravelerId(int $travelerId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function findById(int $id): ?TravelBookEntry;
    
    public function findByUuid(string $uuid): ?TravelBookEntry;
    
    public function create(array $data): TravelBookEntry;
    
    public function update(TravelBookEntry $entry, array $data): TravelBookEntry;
    
    public function delete(TravelBookEntry $entry): bool;
    
    public function addPhotos(TravelBookEntry $entry, array $photoUrls): TravelBookEntry;
    
    public function getByDateRange(int $travelerId, string $startDate, string $endDate): Collection;
}

