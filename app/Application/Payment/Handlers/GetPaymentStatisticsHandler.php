<?php

namespace App\Application\Payment\Handlers;

use App\Application\Payment\Queries\GetPaymentStatisticsQuery;
use App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface;

class GetPaymentStatisticsHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function handle(GetPaymentStatisticsQuery $query): array
    {
        return $this->paymentRepository->getStatistics();
    }
}
