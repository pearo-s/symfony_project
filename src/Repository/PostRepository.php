<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function createSearchBuilder(?string $search, QueryBuilder $query): QueryBuilder
    {
        if ($search !== '') {
            $query->andWhere('p.title LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        return $query;
    }
}
