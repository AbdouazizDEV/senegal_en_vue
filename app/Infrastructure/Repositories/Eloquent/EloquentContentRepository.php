<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Content\Enums\ContentStatus;
use App\Domain\Content\Models\BlogPost;
use App\Domain\Content\Models\HeritageStory;
use App\Infrastructure\Repositories\Contracts\ContentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EloquentContentRepository implements ContentRepositoryInterface
{
    public function getAllHeritageStories(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = HeritageStory::with('creator');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findHeritageStoryById(int $id): ?HeritageStory
    {
        return HeritageStory::with('creator')->find($id);
    }

    public function createHeritageStory(array $data): HeritageStory
    {
        if (empty($data['slug']) && isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        return HeritageStory::create($data);
    }

    public function updateHeritageStory(HeritageStory $story, array $data): HeritageStory
    {
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        $story->update($data);
        return $story->fresh('creator');
    }

    public function deleteHeritageStory(HeritageStory $story): bool
    {
        return $story->delete();
    }

    public function getAllBlogPosts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = BlogPost::with('author');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findBlogPostById(int $id): ?BlogPost
    {
        return BlogPost::with('author')->find($id);
    }

    public function createBlogPost(array $data): BlogPost
    {
        if (empty($data['slug']) && isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        return BlogPost::create($data);
    }

    public function updateBlogPost(BlogPost $post, array $data): BlogPost
    {
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        $post->update($data);
        return $post->fresh('author');
    }

    public function deleteBlogPost(BlogPost $post): bool
    {
        return $post->delete();
    }
}

