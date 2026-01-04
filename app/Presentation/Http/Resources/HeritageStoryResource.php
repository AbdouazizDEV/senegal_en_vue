<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Content\Models\HeritageStory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeritageStoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var HeritageStory $story */
        $story = $this->resource;

        return [
            'id' => $story->id,
            'uuid' => $story->uuid,
            'title' => $story->title,
            'slug' => $story->slug,
            'content' => $story->content,
            'excerpt' => $story->excerpt,
            'author_name' => $story->author_name,
            'author_location' => $story->author_location,
            'images' => $story->images,
            'tags' => $story->tags,
            'status' => $story->status->value,
            'status_label' => $story->status->label(),
            'is_featured' => $story->is_featured,
            'views_count' => $story->views_count,
            'likes_count' => $story->likes_count,
            'creator' => $story->relationLoaded('creator') 
                ? new UserResource($story->creator) 
                : null,
            'published_at' => $story->published_at?->toIso8601String(),
            'created_at' => $story->created_at->toIso8601String(),
            'updated_at' => $story->updated_at->toIso8601String(),
        ];
    }
}

