<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Experience\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperienceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Experience $experience */
        $experience = $this->resource;

        return [
            'id' => $experience->id,
            'uuid' => $experience->uuid,
            'title' => $experience->title,
            'description' => $experience->description,
            'short_description' => $experience->short_description,
            'slug' => $experience->slug,
            'type' => $experience->type->value,
            'type_label' => $experience->type->label(),
            'status' => $experience->status->value,
            'status_label' => $experience->status->label(),
            'price' => $experience->price,
            'currency' => $experience->currency,
            'duration_minutes' => $experience->duration_minutes,
            'max_participants' => $experience->max_participants,
            'min_participants' => $experience->min_participants,
            'images' => $experience->images,
            'location' => $experience->location,
            'schedule' => $experience->schedule,
            'tags' => $experience->tags,
            'amenities' => $experience->amenities,
            'is_featured' => $experience->is_featured,
            'views_count' => $experience->views_count,
            'bookings_count' => $experience->bookings_count,
            'rating' => $experience->rating,
            'reviews_count' => $experience->reviews_count,
            'rejection_reason' => $experience->rejection_reason,
            'published_at' => $experience->published_at?->toIso8601String(),
            'approved_at' => $experience->approved_at?->toIso8601String(),
            'rejected_at' => $experience->rejected_at?->toIso8601String(),
            'provider' => $experience->relationLoaded('provider') 
                ? new UserResource($experience->provider) 
                : null,
            'reports_count' => $experience->relationLoaded('reports') 
                ? $experience->reports->count() 
                : null,
            'created_at' => $experience->created_at->toIso8601String(),
            'updated_at' => $experience->updated_at->toIso8601String(),
        ];
    }
}

