<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    public function findById(int $id): ?Booking;
    
    public function findByUuid(string $uuid): ?Booking;
    
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function getDisputes(int $perPage = 15): LengthAwarePaginator;
    
    public function updateStatus(Booking $booking, BookingStatus $status, ?string $reason = null): Booking;
    
    public function cancel(Booking $booking, ?string $reason = null, ?int $cancelledBy = null): Booking;
    
    public function getStatistics(): array;
}

