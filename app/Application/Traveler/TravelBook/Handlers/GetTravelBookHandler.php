<?php

namespace App\Application\Traveler\TravelBook\Handlers;

use App\Application\Traveler\TravelBook\Queries\GetTravelBookQuery;
use App\Infrastructure\Repositories\Contracts\TravelBookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetTravelBookHandler
{
    public function __construct(
        private TravelBookRepositoryInterface $travelBookRepository
    ) {}

    public function handle(GetTravelBookQuery $query): LengthAwarePaginator
    {
        return $this->travelBookRepository->findByTravelerId(
            $query->travelerId,
            $query->filters,
            $query->perPage
        );
    }
}


