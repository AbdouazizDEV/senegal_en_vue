<?php

namespace App\Application\Experience\Handlers;

use App\Application\Experience\Queries\GetAllExperiencesQuery;
use App\Infrastructure\Repositories\Contracts\ExperienceRepositoryInterface;

class GetAllExperiencesHandler
{
    public function __construct(
        private ExperienceRepositoryInterface $experienceRepository
    ) {}

    public function handle(GetAllExperiencesQuery $query)
    {
        $filters = array_filter([
            'status' => $query->status,
            'type' => $query->type,
            'provider_id' => $query->providerId,
            'search' => $query->search,
            'is_featured' => $query->isFeatured,
        ]);

        return $this->experienceRepository->getAll($filters, $query->perPage);
    }
}

