<?php
namespace App\Application\Certification\Commands;
readonly class CertifyProviderCommand {
    public function __construct(public int $providerId, public int $certificationId, public array $data) {}
}
