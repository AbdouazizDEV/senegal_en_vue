<?php
namespace App\Application\Payment\Queries;
readonly class GetPaymentDisputesQuery {
    public function __construct(public int $page = 1, public int $perPage = 15) {}
}
