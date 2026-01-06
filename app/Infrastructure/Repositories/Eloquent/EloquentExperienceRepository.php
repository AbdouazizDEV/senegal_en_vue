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

    public function checkAvailability(int $experienceId, \DateTime $bookingDate, int $participantsCount): bool
    {
        $experience = $this->findById($experienceId);
        
        if (!$experience) {
            return false;
        }

        // Vérifier que la date n'est pas dans le passé
        $today = new \DateTime('today');
        if ($bookingDate < $today) {
            return false;
        }

        // Vérifier la disponibilité des places si max_participants est défini
        if ($experience->max_participants) {
            // Compter les participants déjà réservés pour cette date
            $existingBookings = \App\Domain\Booking\Models\Booking::where('experience_id', $experienceId)
                ->whereDate('booking_date', $bookingDate->format('Y-m-d'))
                ->whereIn('status', [
                    \App\Domain\Booking\Enums\BookingStatus::PENDING,
                    \App\Domain\Booking\Enums\BookingStatus::CONFIRMED
                ])
                ->sum('participants_count');

            $availableSlots = $experience->max_participants - $existingBookings;
            
            if ($participantsCount > $availableSlots) {
                return false;
            }
        }

        return true;
    }
}

