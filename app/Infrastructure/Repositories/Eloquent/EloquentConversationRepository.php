<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Message\Models\Conversation;
use App\Infrastructure\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentConversationRepository implements ConversationRepositoryInterface
{
    public function findByTravelerId(int $travelerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Conversation::with(['provider', 'experience', 'booking', 'latestMessage'])
            ->where('traveler_id', $travelerId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        return $query->orderBy('last_message_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Conversation
    {
        return Conversation::with(['traveler', 'provider', 'experience', 'booking', 'messages.sender', 'messages.receiver'])
            ->find($id);
    }

    public function findByUuid(string $uuid): ?Conversation
    {
        return Conversation::with(['traveler', 'provider', 'experience', 'booking', 'messages.sender', 'messages.receiver'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function findByTravelerAndProvider(int $travelerId, int $providerId, ?int $experienceId = null): ?Conversation
    {
        $query = Conversation::where('traveler_id', $travelerId)
            ->where('provider_id', $providerId);

        if ($experienceId) {
            $query->where('experience_id', $experienceId);
        }

        return $query->first();
    }

    public function create(array $data): Conversation
    {
        return Conversation::create($data);
    }

    public function update(Conversation $conversation, array $data): Conversation
    {
        $conversation->update($data);
        return $conversation->fresh(['traveler', 'provider', 'experience', 'booking']);
    }

    public function getUnreadCount(int $travelerId): int
    {
        return Conversation::where('traveler_id', $travelerId)
            ->where('unread_count_traveler', '>', 0)
            ->count();
    }
}


