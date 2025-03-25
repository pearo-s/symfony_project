<?php

namespace App\Repository;

use App\Entity\Commentary;
use App\Entity\CommentaryLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<CommentaryLike>
 */
class CommentaryLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentaryLike::class);
    }

    public function isCommentaryLikeByUser(Commentary $commentary, UserInterface $user): bool
    {
        return (bool) $this->createQueryBuilder('cl')
            ->select('COUNT(cl.id)')
            ->where('cl.commentary = :commentary')
            ->andWhere('cl.user = :user')
            ->setParameter('commentary', $commentary)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
