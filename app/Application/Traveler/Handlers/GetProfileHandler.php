<?php

namespace App\Application\Traveler\Handlers;

use App\Application\Traveler\Queries\GetProfileQuery;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;

class GetProfileHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(GetProfileQuery $query)
    {
        return $this->userRepository->findById($query->userId);
    }
}

