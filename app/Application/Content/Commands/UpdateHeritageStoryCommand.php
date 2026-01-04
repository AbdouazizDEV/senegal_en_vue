<?php
namespace App\Application\Content\Commands;
readonly class UpdateHeritageStoryCommand {
    public function __construct(public int $storyId, public array $data) {}
}
