<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Review\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Review $review */
        $review = $this->resource;

        return [
            'id' => $review->id,
            'uuid' => $review->uuid,
            'rating' => $review->rating,
            'title' => $review->title,
            'comment' => $review->comment,
            'status' => $review->status->value,
            'status_label' => $review->status->label(),
            'is_verified' => $review->is_verified,
            'is_featured' => $review->is_featured,
            'helpful_count' => $review->helpful_count,
            'rejection_reason' => $review->rejection_reason,
            'approved_at' => $review->approved_at?->toIso8601String(),
            'rejected_at' => $review->rejected_at?->toIso8601String(),
            'images' => $review->images,
            'experience' => $review->relationLoaded('experience') 
                ? new ExperienceResource($review->experience) 
                : null,
            'traveler' => $review->relationLoaded('traveler') 
                ? new UserResource($review->traveler) 
                : null,
            'provider' => $review->relationLoaded('provider') 
                ? new UserResource($review->provider) 
                : null,
            'reports_count' => $review->relationLoaded('reports') 
                ? $review->reports->count() 
                : null,
            'created_at' => $review->created_at->toIso8601String(),
            'updated_at' => $review->updated_at->toIso8601String(),
        ];
    }
}

