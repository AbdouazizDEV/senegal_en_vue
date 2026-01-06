<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Message\Models\Message;
use App\Infrastructure\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentMessageRepository implements MessageRepositoryInterface
{
    public function findByConversationId(int $conversationId, int $perPage = 50): LengthAwarePaginator
    {
        return Message::with(['sender', 'receiver'])
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Message
    {
        return Message::with(['conversation', 'sender', 'receiver'])->find($id);
    }

    public function create(array $data): Message
    {
        return Message::create($data);
    }

    public function markAsRead(Message $message): Message
    {
        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
        return $message->fresh(['sender', 'receiver']);
    }

    public function markConversationAsRead(int $conversationId, int $userId): int
    {
        return Message::where('conversation_id', $conversationId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function getUnreadCount(int $userId): int
    {
        return Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }
}


