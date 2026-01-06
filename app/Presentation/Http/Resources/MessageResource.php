<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Message\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Message $this */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'content' => $this->content,
            'type' => $this->type,
            'attachments' => $this->attachments ?? [],
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->toISOString(),
            'sender' => $this->relationLoaded('sender') 
                ? new UserResource($this->sender) 
                : null,
            'receiver' => $this->relationLoaded('receiver') 
                ? new UserResource($this->receiver) 
                : null,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}


