<?php

namespace App\Repository;

use App\Entity\Commentary;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentary>
 */
class CommentaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentary::class);
    }

    public function sortCommentariesByDate(Post $post): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.post = :post')
            ->setParameter('post', $post)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function sortCommentaryByLikes($post)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.commentaryLikes', 'likes')
            ->where('c.post = :post')
            ->setParameter('post', $post)
            ->groupBy('c.id')
            ->orderBy('COUNT(likes.id)', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
