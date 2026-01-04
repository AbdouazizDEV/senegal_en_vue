<?php
namespace App\Application\Content\Commands;
readonly class CreateBlogPostCommand {
    public function __construct(public array $data) {}
}
