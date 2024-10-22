<?php

namespace App\Repository;

use App\Entity\Post;
use App\Interfaces\PostRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository implements PostRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function add(Post $post, bool $flush = false): void
    {
        $this->_em->persist($post);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Post $post, bool $flush = false): void
    {
        $this->_em->remove($post);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?Post
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function findByCriteria(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return parent::createQueryBuilder($alias, $indexBy);
    }
}
