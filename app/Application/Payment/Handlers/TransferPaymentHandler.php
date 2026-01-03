<?php

namespace App\Application\Payment\Handlers;

use App\Application\Payment\Commands\TransferPaymentCommand;
use App\Infrastructure\Repositories\Contracts\PaymentRepositoryInterface;

class TransferPaymentHandler
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function handle(TransferPaymentCommand $command)
    {
        $payment = $this->paymentRepository->findById($command->paymentId);
        
        if (!$payment) {
            throw new \RuntimeException('Paiement non trouvé');
        }

        return $this->paymentRepository->transfer($payment);
    }
}
