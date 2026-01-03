<?php

namespace App\Infrastructure\Repositories\Contracts;

use App\Domain\Payment\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment;
    
    public function findByUuid(string $uuid): ?Payment;
    
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function getDisputes(int $perPage = 15): LengthAwarePaginator;
    
    public function refund(Payment $payment, float $amount, ?string $reason = null): Payment;
    
    public function transfer(Payment $payment): Payment;
    
    public function getStatistics(): array;
    
    public function getCommissions(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}

