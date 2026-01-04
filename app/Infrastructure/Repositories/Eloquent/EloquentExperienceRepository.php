<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Experience\Enums\ExperienceStatus;
use App\Domain\Experience\Models\Experience;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentExperienceRepository implements ExperienceRepositoryInterface
{
    public function findById(int $id): ?Experience
    {
        return Experience::with(['provider', 'reports.reporter'])->find($id);
    }

    public function findByUuid(string $uuid): ?Experience
    {
        return Experience::with(['provider', 'reports.reporter'])->where('uuid', $uuid)->first();
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Experience::with(['provider']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getPending(int $perPage = 15): LengthAwarePaginator
    {
        return Experience::with(['provider'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    public function getReported(int $perPage = 15): LengthAwarePaginator
    {
        return Experience::with(['provider', 'reports.reporter'])
            ->reported()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function update(Experience $experience, array $data): Experience
    {
        $experience->update($data);
        return $experience->fresh(['provider']);
    }

    public function delete(Experience $experience): bool
    {
        return $experience->delete();
    }

    public function moderate(Experience $experience, ExperienceStatus $status, ?string $reason = null): Experience
    {
        $data = ['status' => $status];
        
        if ($status === ExperienceStatus::APPROVED) {
            $data['approved_at'] = now();
            $data['published_at'] = now();
        } elseif ($status === ExperienceStatus::REJECTED) {
            $data['rejected_at'] = now();
            if ($reason) {
                $data['rejection_reason'] = $reason;
            }
        }

        $experience->update($data);
        return $experience->fresh(['provider']);
    }

    public function getReports(int $perPage = 15): LengthAwarePaginator
    {
        return \App\Domain\Experience\Models\ExperienceReport::with(['experience.provider', 'reporter', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Experience::with(['provider'])
            ->where('status', ExperienceStatus::APPROVED);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['region'])) {
            $query->whereJsonContains('location->region', $filters['region']);
        }

        if (isset($filters['city'])) {
            $query->whereJsonContains('location->city', $filters['city']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['tags'])) {
            $tags = is_array($filters['tags']) ? $filters['tags'] : [$filters['tags']];
            foreach ($tags as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getFeatured(int $limit = 10): Collection
    {
        return Experience::with(['provider'])
            ->where('status', ExperienceStatus::APPROVED)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return Experience::with(['provider'])
            ->where('status', ExperienceStatus::APPROVED)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByRegion(string $region, int $perPage = 15): LengthAwarePaginator
    {
        return Experience::with(['provider'])
            ->where('status', ExperienceStatus::APPROVED)
            ->whereJsonContains('location->region', $region)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByTheme(string $theme, int $perPage = 15): LengthAwarePaginator
    {
        return Experience::with(['provider'])
            ->where('status', ExperienceStatus::APPROVED)
            ->whereJsonContains('tags', $theme)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByPriceRange(float $minPrice, float $maxPrice, int $perPage = 15): LengthAwarePaginator
    {
        return Experience::with(['provider'])
            ->where('status', ExperienceStatus::APPROVED)
            ->whereBetween('price', [$minPrice, $maxPrice])
            ->orderBy('price', 'asc')
            ->paginate($perPage);
    }

    public function getPhotos(int $experienceId): array
    {
        $experience = $this->findById($experienceId);
        return $experience ? ($experience->images ?? []) : [];
    }

    public function getSimilar(int $experienceId, int $limit = 5): Collection
    {
        $experience = $this->findById($experienceId);
        
        if (!$experience) {
            return collect();
        }

        $tags = $experience->tags ?? [];
        $type = $experience->type;

        return Experience::with(['provider'])
            ->where('status', ExperienceStatus::APPROVED)
            ->where('id', '!=', $experienceId)
            ->where(function ($query) use ($tags, $type) {
                $query->where('type', $type);
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        $query->orWhereJsonContains('tags', $tag);
                    }
                }
            })
            ->limit($limit)
            ->get();
    }

    public function checkAvailability(int $experienceId, \DateTime $date, int $participants): bool
    {
        $experience = $this->findById($experienceId);
        
        if (!$experience) {
            return false;
        }

        // Vérifier les participants min/max
        if ($experience->min_participants && $participants < $experience->min_participants) {
            return false;
        }

        if ($experience->max_participants && $participants > $experience->max_participants) {
            return false;
        }

        // Vérifier les réservations existantes pour cette date
        $existingBookings = \App\Domain\Booking\Models\Booking::where('experience_id', $experienceId)
            ->whereDate('booking_date', $date->format('Y-m-d'))
            ->whereIn('status', ['pending', 'confirmed'])
            ->sum('participants_count');

        $availableSlots = ($experience->max_participants ?? 999) - $existingBookings;
        
        return $availableSlots >= $participants;
    }
}

