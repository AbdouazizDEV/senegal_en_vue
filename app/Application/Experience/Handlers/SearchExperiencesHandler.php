<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\SearchExperiencesQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class SearchExperiencesHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(SearchExperiencesQuery $query)
    {
        $filters = array_filter([
            'search' => $query->search,
            'type' => $query->type,
            'region' => $query->region,
            'city' => $query->city,
            'min_price' => $query->minPrice,
            'max_price' => $query->maxPrice,
            'tags' => $query->tags,
        ]);

        return $this->experienceRepository->search($filters, $query->perPage);
    }
}

