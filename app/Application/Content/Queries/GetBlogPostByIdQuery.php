<?php
namespace App\Application\Content\Queries;
readonly class GetBlogPostByIdQuery {
    public function __construct(public int $postId) {}
}
