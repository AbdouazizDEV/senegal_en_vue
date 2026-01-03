<?php
namespace App\Application\Payment\Commands;
readonly class RefundPaymentCommand {
    public function __construct(
        public int $paymentId,
        public float $amount,
        public ?string $reason = null
    ) {}
}
