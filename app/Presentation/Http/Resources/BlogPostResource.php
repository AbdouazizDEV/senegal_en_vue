<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Content\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var BlogPost $post */
        $post = $this->resource;

        return [
            'id' => $post->id,
            'uuid' => $post->uuid,
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'excerpt' => $post->excerpt,
            'featured_image' => $post->featured_image,
            'images' => $post->images,
            'tags' => $post->tags,
            'status' => $post->status->value,
            'status_label' => $post->status->label(),
            'is_featured' => $post->is_featured,
            'views_count' => $post->views_count,
            'likes_count' => $post->likes_count,
            'author' => $post->relationLoaded('author') 
                ? new UserResource($post->author) 
                : null,
            'published_at' => $post->published_at?->toIso8601String(),
            'created_at' => $post->created_at->toIso8601String(),
            'updated_at' => $post->updated_at->toIso8601String(),
        ];
    }
}

