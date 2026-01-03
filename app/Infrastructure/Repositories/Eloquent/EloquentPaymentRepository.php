<?php

namespace App\Infrastructure\Repositories\Eloquent;

use App\Domain\Payment\Models\Payment;
use App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment
    {
        return Payment::with(['booking', 'traveler', 'provider', 'disputes'])->find($id);
    }

    public function findByUuid(string $uuid): ?Payment
    {
        return Payment::with(['booking', 'traveler', 'provider', 'disputes'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::with(['booking', 'traveler', 'provider']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['booking_id'])) {
            $query->where('booking_id', $filters['booking_id']);
        }

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getDisputes(int $perPage = 15): LengthAwarePaginator
    {
        return \App\Domain\Payment\Models\PaymentDispute::with([
            'payment.booking',
            'payment.traveler',
            'payment.provider',
            'initiatedBy',
            'resolvedBy'
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function refund(Payment $payment, float $amount, ?string $reason = null): Payment
    {
        $isFullRefund = $amount >= $payment->amount;
        
        $data = [
            'status' => $isFullRefund 
                ? \App\Domain\Payment\Enums\PaymentStatus::REFUNDED 
                : \App\Domain\Payment\Enums\PaymentStatus::PARTIALLY_REFUNDED,
            'refund_reason' => $reason,
        ];

        $payment->update($data);
        return $payment->fresh(['booking', 'traveler', 'provider']);
    }

    public function transfer(Payment $payment): Payment
    {
        $payment->update([
            'transferred_at' => now(),
        ]);
        
        return $payment->fresh(['booking', 'traveler', 'provider']);
    }

    public function getStatistics(): array
    {
        $total = Payment::count();
        $totalAmount = Payment::where('status', 'completed')->sum('amount');
        $totalCommission = Payment::where('status', 'completed')->sum('commission_amount');
        $totalRefunded = Payment::whereIn('status', ['refunded', 'partially_refunded'])->sum('amount');
        
        $byStatus = Payment::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $today = Payment::whereDate('created_at', today())->count();
        $thisWeek = Payment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = Payment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $disputesCount = \App\Domain\Payment\Models\PaymentDispute::where('status', '!=', 'closed')->count();
        $pendingTransfers = Payment::where('status', 'completed')
            ->whereNull('transferred_at')
            ->count();

        return [
            'total' => $total,
            'total_amount' => $totalAmount,
            'total_commission' => $totalCommission,
            'total_refunded' => $totalRefunded,
            'by_status' => $byStatus,
            'payments' => [
                'today' => $today,
                'this_week' => $thisWeek,
                'this_month' => $thisMonth,
            ],
            'disputes_count' => $disputesCount,
            'pending_transfers' => $pendingTransfers,
        ];
    }

    public function getCommissions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::with(['provider', 'booking'])
            ->where('status', 'completed')
            ->where('commission_amount', '>', 0);

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}

