<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Notification\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function findByUserId(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function findById(int $id): ?Notification;
    
    public function create(array $data): Notification;
    
    public function markAsRead(Notification $notification): Notification;
    
    public function markAllAsRead(int $userId): int;
    
    public function getUnreadCount(int $userId): int;
    
    public function getUnread(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection;
}


