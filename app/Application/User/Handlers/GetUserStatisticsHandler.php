<?php

namespace App\Application\User\Handlers;

use App\Application\User\Queries\GetUserStatisticsQuery;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class GetUserStatisticsHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(GetUserStatisticsQuery $query): array
    {
        return $this->userRepository->getStatistics();
    }
}


