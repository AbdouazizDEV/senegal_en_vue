<?php
namespace App\Application\Payment\Queries;
readonly class GetPaymentByIdQuery {
    public function __construct(public int $paymentId) {}
}
