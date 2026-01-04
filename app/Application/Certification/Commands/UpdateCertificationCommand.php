<?php
namespace App\Application\Certification\Commands;
readonly class UpdateCertificationCommand {
    public function __construct(public int $certificationId, public array $data) {}
}
