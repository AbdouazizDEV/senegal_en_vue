<?php

namespace App\Application\User\Queries;

readonly class GetAllUsersQuery
{
    public function __construct(
        public ?string $role = null,
        public ?string $status = null,
        public ?string $search = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}


