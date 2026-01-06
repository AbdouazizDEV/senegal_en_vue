<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Message\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface
{
    public function findByConversationId(int $conversationId, int $perPage = 50): LengthAwarePaginator;
    
    public function findById(int $id): ?Message;
    
    public function create(array $data): Message;
    
    public function markAsRead(Message $message): Message;
    
    public function markConversationAsRead(int $conversationId, int $userId): int;
    
    public function getUnreadCount(int $userId): int;
}


