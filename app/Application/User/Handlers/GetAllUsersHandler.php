<?php

namespace App\Application\User\Handlers;

use App\Application\User\Queries\GetAllUsersQuery;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAllUsersHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(GetAllUsersQuery $query): LengthAwarePaginator
    {
        $filters = [];
        
        if ($query->role) {
            $filters['role'] = $query->role;
        }
        
        if ($query->status) {
            $filters['status'] = $query->status;
        }
        
        if ($query->search) {
            $filters['search'] = $query->search;
        }
        
        return $this->userRepository->getAll($filters, $query->perPage);
    }
}


