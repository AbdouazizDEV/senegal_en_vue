<?php

namespace App\Presentation\Http\Resources;

use App\Domain\TravelBook\Models\TravelBookEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelBookEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var TravelBookEntry $this */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'content' => $this->content,
            'entry_date' => $this->entry_date?->format('Y-m-d'),
            'location' => $this->location,
            'location_details' => $this->location_details,
            'photos' => $this->photos ?? [],
            'tags' => $this->tags ?? [],
            'visibility' => $this->visibility,
            'visibility_label' => $this->getVisibilityLabel(),
            'is_featured' => $this->is_featured,
            'views_count' => $this->views_count,
            'metadata' => $this->metadata,
            'experience' => $this->relationLoaded('experience') 
                ? new ExperienceResource($this->experience) 
                : null,
            'booking' => $this->relationLoaded('booking') 
                ? new BookingResource($this->booking) 
                : null,
            'traveler' => $this->relationLoaded('traveler') 
                ? new UserResource($this->traveler) 
                : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function getVisibilityLabel(): string
    {
        return match($this->visibility) {
            'private' => 'Privé',
            'friends' => 'Amis',
            'public' => 'Public',
            default => 'Privé',
        };
    }
}

