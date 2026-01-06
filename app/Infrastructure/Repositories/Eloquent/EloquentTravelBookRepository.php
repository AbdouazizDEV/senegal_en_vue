<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\TravelBook\Models\TravelBookEntry;
use App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentTravelBookRepository implements TravelBookRepositoryInterface
{
    public function findByTravelerId(int $travelerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TravelBookEntry::with(['experience', 'booking', 'traveler'])
            ->where('traveler_id', $travelerId);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if (isset($filters['experience_id'])) {
            $query->where('experience_id', $filters['experience_id']);
        }

        if (isset($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        if (isset($filters['start_date'])) {
            $query->where('entry_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('entry_date', '<=', $filters['end_date']);
        }

        if (isset($filters['tags'])) {
            $tags = is_array($filters['tags']) ? $filters['tags'] : [$filters['tags']];
            foreach ($tags as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        $orderBy = $filters['order_by'] ?? 'entry_date';
        $orderDirection = $filters['order_direction'] ?? 'desc';

        return $query->orderBy($orderBy, $orderDirection)->paginate($perPage);
    }

    public function findById(int $id): ?TravelBookEntry
    {
        return TravelBookEntry::with(['experience', 'booking', 'traveler'])->find($id);
    }

    public function findByUuid(string $uuid): ?TravelBookEntry
    {
        return TravelBookEntry::with(['experience', 'booking', 'traveler'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function create(array $data): TravelBookEntry
    {
        return TravelBookEntry::create($data);
    }

    public function update(TravelBookEntry $entry, array $data): TravelBookEntry
    {
        $entry->update($data);
        return $entry->fresh(['experience', 'booking', 'traveler']);
    }

    public function delete(TravelBookEntry $entry): bool
    {
        return $entry->delete();
    }

    public function addPhotos(TravelBookEntry $entry, array $photoUrls): TravelBookEntry
    {
        $existingPhotos = $entry->photos ?? [];
        $entry->photos = array_merge($existingPhotos, $photoUrls);
        $entry->save();
        return $entry->fresh(['experience', 'booking', 'traveler']);
    }

    public function getByDateRange(int $travelerId, string $startDate, string $endDate): Collection
    {
        return TravelBookEntry::with(['experience', 'booking', 'traveler'])
            ->where('traveler_id', $travelerId)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('entry_date', 'asc')
            ->get();
    }
}

