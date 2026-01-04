<?php
namespace App\Application\Experience\Queries;
readonly class GetExperiencesByThemeQuery {
    public function __construct(
        public string $theme,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}
