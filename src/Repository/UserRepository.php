<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function createSearchAndSortQueryBuilder(?string $search, string $sort = 'u.id', string $direction = 'asc'): QueryBuilder
    {
        $query = $this->createQueryBuilder('u');

        if ($search !== '') {
            $query->andWhere('u.name LIKE :q OR u.surname LIKE :q OR u.username LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        $query->orderBy($sort, $direction);

        return $query;
    }
}
