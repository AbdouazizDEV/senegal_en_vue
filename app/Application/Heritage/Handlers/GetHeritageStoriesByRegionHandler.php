<?php

namespace App\Application\Heritage\Handlers;

use App\Application\Heritage\Queries\GetHeritageStoriesByRegionQuery;
use App\Domain\Content\Models\HeritageStory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetHeritageStoriesByRegionHandler
{
    public function handle(GetHeritageStoriesByRegionQuery $query): LengthAwarePaginator
    {
        return HeritageStory::with(['creator'])
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->whereJsonContains('tags', $query->region)
                  ->orWhere('author_location', 'like', "%{$query->region}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($query->perPage);
    }
}

