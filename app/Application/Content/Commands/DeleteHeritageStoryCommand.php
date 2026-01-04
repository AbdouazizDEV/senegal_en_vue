<?php
namespace App\Application\Content\Commands;
readonly class DeleteHeritageStoryCommand {
    public function __construct(public int $storyId) {}
}
