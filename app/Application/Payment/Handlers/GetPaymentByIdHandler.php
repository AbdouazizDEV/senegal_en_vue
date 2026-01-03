<?php

namespace App\Application\Payment\Handlers;

use App\Application\Payment\Queries\GetPaymentByIdQuery;
use App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface;

class GetPaymentByIdHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function handle(GetPaymentByIdQuery $query)
    {
        return $this->paymentRepository->findById($query->paymentId);
    }
}
