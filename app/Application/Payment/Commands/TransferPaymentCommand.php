<?php
namespace App\Application\Payment\Commands;
readonly class TransferPaymentCommand {
    public function __construct(public int $paymentId) {}
}
