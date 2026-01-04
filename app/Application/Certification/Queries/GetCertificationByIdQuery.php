<?php
namespace App\Application\Certification\Queries;
readonly class GetCertificationByIdQuery {
    public function __construct(public int $certificationId) {}
}
