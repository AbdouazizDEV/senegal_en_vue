<?php
namespace App\Application\Certification\Queries;
readonly class GetAllCertificationsQuery {
    public function __construct(
        public ?string $type = null,
        public ?bool $isActive = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}
