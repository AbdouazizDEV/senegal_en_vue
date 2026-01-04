<?php

namespace App\Application\Traveler\Queries;

readonly class GetProfileQuery
{
    public function __construct(public int $userId) {}
}

