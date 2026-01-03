<?php

namespace App\Application\Payment\Handlers;

use App\Application\Payment\Commands\RefundPaymentCommand;
use App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface;

class RefundPaymentHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function handle(RefundPaymentCommand $command)
    {
        $payment = $this->paymentRepository->findById($command->paymentId);
        
        if (!$payment) {
            throw new \RuntimeException('Paiement non trouvé');
        }

        return $this->paymentRepository->refund($payment, $command->amount, $command->reason);
    }
}
