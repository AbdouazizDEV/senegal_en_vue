<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Models\Booking;
use App\Infrastructure\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentBookingRepository implements BookingRepositoryInterface
{
    public function findById(int $id): ?Booking
    {
        return Booking::with(['experience', 'traveler', 'provider', 'disputes.initiatedBy'])
            ->find($id);
    }

    public function findByUuid(string $uuid): ?Booking
    {
        return Booking::with(['experience', 'traveler', 'provider', 'disputes.initiatedBy'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::with(['experience', 'traveler', 'provider']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['experience_id'])) {
            $query->where('experience_id', $filters['experience_id']);
        }

        if (isset($filters['traveler_id'])) {
            $query->where('traveler_id', $filters['traveler_id']);
        }

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('booking_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('booking_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getDisputes(int $perPage = 15): LengthAwarePaginator
    {
        return \App\Domain\Booking\Models\BookingDispute::with([
            'booking.experience',
            'booking.traveler',
            'booking.provider',
            'initiatedBy',
            'resolvedBy'
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function updateStatus(Booking $booking, BookingStatus $status, ?string $reason = null): Booking
    {
        $data = ['status' => $status];

        if ($status === BookingStatus::CONFIRMED) {
            $data['confirmed_at'] = now();
        } elseif ($status === BookingStatus::COMPLETED) {
            $data['completed_at'] = now();
        } elseif ($status === BookingStatus::CANCELLED) {
            $data['cancelled_at'] = now();
            if ($reason) {
                $data['cancellation_reason'] = $reason;
            }
        }

        $booking->update($data);
        return $booking->fresh(['experience', 'traveler', 'provider']);
    }

    public function cancel(Booking $booking, ?string $reason = null, ?int $cancelledBy = null): Booking
    {
        $data = [
            'status' => BookingStatus::CANCELLED,
            'cancelled_at' => now(),
        ];

        if ($reason) {
            $data['cancellation_reason'] = $reason;
        }

        if ($cancelledBy) {
            $data['cancelled_by'] = $cancelledBy;
        }

        $booking->update($data);
        return $booking->fresh(['experience', 'traveler', 'provider']);
    }

    public function getStatistics(): array
    {
        $total = Booking::count();
        $byStatus = Booking::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byPaymentStatus = Booking::selectRaw('payment_status, COUNT(*) as count')
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status')
            ->toArray();

        $today = Booking::whereDate('created_at', today())->count();
        $thisWeek = Booking::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = Booking::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalRevenue = Booking::where('payment_status', 'paid')
            ->sum('total_amount');

        $disputesCount = \App\Domain\Booking\Models\BookingDispute::where('status', '!=', 'closed')->count();

        return [
            'total' => $total,
            'by_status' => $byStatus,
            'by_payment_status' => $byPaymentStatus,
            'bookings' => [
                'today' => $today,
                'this_week' => $thisWeek,
                'this_month' => $thisMonth,
            ],
            'total_revenue' => $totalRevenue,
            'disputes_count' => $disputesCount,
        ];
    }

    public function findByTraveler(int $travelerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::where('traveler_id', $travelerId)
            ->with(['experience', 'provider']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('booking_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('booking_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getUpcomingByTraveler(int $travelerId, int $perPage = 15): LengthAwarePaginator
    {
        return Booking::where('traveler_id', $travelerId)
            ->where('booking_date', '>=', now()->toDateString())
            ->whereIn('status', [BookingStatus::PENDING, BookingStatus::CONFIRMED])
            ->with(['experience', 'provider'])
            ->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->paginate($perPage);
    }

    public function getPendingByTraveler(int $travelerId, int $perPage = 15): LengthAwarePaginator
    {
        return Booking::where('traveler_id', $travelerId)
            ->where('status', BookingStatus::PENDING)
            ->with(['experience', 'provider'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getConfirmedByTraveler(int $travelerId, int $perPage = 15): LengthAwarePaginator
    {
        return Booking::where('traveler_id', $travelerId)
            ->where('status', BookingStatus::CONFIRMED)
            ->with(['experience', 'provider'])
            ->orderBy('booking_date', 'asc')
            ->paginate($perPage);
    }

    public function getHistoryByTraveler(int $travelerId, int $perPage = 15): LengthAwarePaginator
    {
        return Booking::where('traveler_id', $travelerId)
            ->whereIn('status', [BookingStatus::COMPLETED, BookingStatus::CANCELLED, BookingStatus::REFUNDED])
            ->with(['experience', 'provider'])
            ->orderBy('booking_date', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Booking
    {
        return Booking::create($data);
    }
}

