<?php

namespace App\Application\Payment\Handlers;

use App\Application\Payment\Queries\GetCommissionsQuery;
use App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface;

class GetCommissionsHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function handle(GetCommissionsQuery $query)
    {
        $filters = array_filter([
            'provider_id' => $query->providerId,
            'date_from' => $query->dateFrom,
            'date_to' => $query->dateTo,
        ]);

        return $this->paymentRepository->getCommissions($filters, $query->perPage);
    }
}
