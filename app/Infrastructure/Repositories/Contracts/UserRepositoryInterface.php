<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\User\Models\User;
use App\Domain\User\Enums\UserRole;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    
    public function findByUuid(string $uuid): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function findByPhone(string $phone): ?User;
    
    public function create(array $data): User;
    
    public function update(string $id, array $data): User;
    
    public function delete(string $id): bool;
    
    public function findByRole(UserRole $role, int $perPage = 15);
}

