<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\User\Models\User;
use App\Domain\User\Enums\UserRole;
use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }
    
    public function findByUuid(string $uuid): ?User
    {
        return User::where('uuid', $uuid)->first();
    }
    
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
    
    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }
    
    public function create(array $data): User
    {
        return User::create($data);
    }
    
    public function update(string $id, array $data): User
    {
        $user = $this->findById($id);
        
        if (!$user) {
            throw new \RuntimeException("User with ID {$id} not found");
        }
        
        $user->update($data);
        
        return $user->fresh();
    }
    
    public function delete(string $id): bool
    {
        $user = $this->findById($id);
        
        if (!$user) {
            return false;
        }
        
        return $user->delete();
    }
    
    public function findByRole(UserRole $role, int $perPage = 15): LengthAwarePaginator
    {
        return User::role($role)->paginate($perPage);
    }
}

