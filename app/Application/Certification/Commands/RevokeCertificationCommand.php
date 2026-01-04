<?php
namespace App\Application\Certification\Commands;
readonly class RevokeCertificationCommand {
    public function __construct(public int $providerId, public int $certificationId) {}
}
