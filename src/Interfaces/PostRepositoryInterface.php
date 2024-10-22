<?php

namespace App\Interfaces;

use App\Entity\Post;

interface PostRepositoryInterface
{
    public function add(Post $post, bool $flush = false): void;
    public function remove(Post $post, bool $flush = false): void;
    public function find($id): ?Post;
    public function findAll(): array;
    public function findByCriteria(array $criteria, array $orderBy = null, $limit = null, $offset = null): array;
    public function createQueryBuilder($alias, $indexBy = null);
}
