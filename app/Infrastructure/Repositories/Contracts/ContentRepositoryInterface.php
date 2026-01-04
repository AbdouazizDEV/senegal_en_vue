<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Content\Models\BlogPost;
use App\Domain\Content\Models\HeritageStory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContentRepositoryInterface
{
    // Heritage Stories
    public function getAllHeritageStories(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function findHeritageStoryById(int $id): ?HeritageStory;
    
    public function createHeritageStory(array $data): HeritageStory;
    
    public function updateHeritageStory(HeritageStory $story, array $data): HeritageStory;
    
    public function deleteHeritageStory(HeritageStory $story): bool;
    
    // Blog Posts
    public function getAllBlogPosts(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function findBlogPostById(int $id): ?BlogPost;
    
    public function createBlogPost(array $data): BlogPost;
    
    public function updateBlogPost(BlogPost $post, array $data): BlogPost;
    
    public function deleteBlogPost(BlogPost $post): bool;
}

