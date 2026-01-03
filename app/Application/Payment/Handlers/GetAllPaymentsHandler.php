<?php

namespace App\Application\Payment\Handlers;

use App\Application\Payment\Queries\GetAllPaymentsQuery;
use App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface;

class GetAllPaymentsHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function handle(GetAllPaymentsQuery $query)
    {
        $filters = array_filter([
            'status' => $query->status,
            'type' => $query->type,
            'booking_id' => $query->bookingId,
            'provider_id' => $query->providerId,
            'date_from' => $query->dateFrom,
            'date_to' => $query->dateTo,
        ]);

        return $this->paymentRepository->getAll($filters, $query->perPage);
    }
}
