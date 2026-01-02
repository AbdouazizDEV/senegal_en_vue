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
    
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query();
        
        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
    
    public function getStatistics(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $pendingUsers = User::where('status', 'pending_verification')->count();
        $suspendedUsers = User::where('status', 'suspended')->count();
        
        $travelers = User::where('role', 'traveler')->count();
        $providers = User::where('role', 'provider')->count();
        $admins = User::where('role', 'admin')->count();
        $institutions = User::where('role', 'institution')->count();
        
        $todayRegistrations = User::whereDate('created_at', today())->count();
        $thisWeekRegistrations = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonthRegistrations = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        return [
            'total' => $totalUsers,
            'by_status' => [
                'active' => $activeUsers,
                'pending_verification' => $pendingUsers,
                'suspended' => $suspendedUsers,
            ],
            'by_role' => [
                'traveler' => $travelers,
                'provider' => $providers,
                'admin' => $admins,
                'institution' => $institutions,
            ],
            'registrations' => [
                'today' => $todayRegistrations,
                'this_week' => $thisWeekRegistrations,
                'this_month' => $thisMonthRegistrations,
            ],
        ];
    }
}

