<?php
namespace App\Application\Experience\Queries;
readonly class CheckAvailabilityQuery {
    public function __construct(
        public int $experienceId,
        public string $date,
        public int $participants
    ) {}
}
