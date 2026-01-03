<?php

namespace App\Application\Payment\Handlers;

use App\Application\Payment\Queries\GetPaymentDisputesQuery;
use App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface;

class GetPaymentDisputesHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function handle(GetPaymentDisputesQuery $query)
    {
        return $this->paymentRepository->getDisputes($query->perPage);
    }
}
