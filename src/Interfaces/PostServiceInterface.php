<?php

namespace App\Interfaces;

use App\Entity\Post;

interface PostServiceInterface
{
    public function createPost(array $data): Post;
    public function updatePost(int $id, array $data): Post;
    public function deletePost(int $id): void;
    public function getPost(int $id): ?Post;
    public function getAllPosts(int $page, int $limit): array;
}
