<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Message\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Conversation $this */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'subject' => $this->subject,
            'status' => $this->status,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'unread_count_traveler' => $this->unread_count_traveler,
            'unread_count_provider' => $this->unread_count_provider,
            'provider' => $this->relationLoaded('provider') 
                ? new UserResource($this->provider) 
                : null,
            'experience' => $this->relationLoaded('experience') 
                ? new ExperienceResource($this->experience) 
                : null,
            'booking' => $this->relationLoaded('booking') 
                ? new BookingResource($this->booking) 
                : null,
            'latest_message' => $this->relationLoaded('latestMessage') && $this->latestMessage->isNotEmpty()
                ? new MessageResource($this->latestMessage->first())
                : null,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}


