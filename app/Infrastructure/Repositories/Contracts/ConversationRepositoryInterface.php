<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Message\Models\Conversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ConversationRepositoryInterface
{
    public function findByTravelerId(int $travelerId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function findById(int $id): ?Conversation;
    
    public function findByUuid(string $uuid): ?Conversation;
    
    public function findByTravelerAndProvider(int $travelerId, int $providerId, ?int $experienceId = null): ?Conversation;
    
    public function create(array $data): Conversation;
    
    public function update(Conversation $conversation, array $data): Conversation;
    
    public function getUnreadCount(int $travelerId): int;
}


