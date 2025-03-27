<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function createSearchAndFilterBuilder(?array $requestData): QueryBuilder
    {
        $query = $this->createQueryBuilder('p');

        if(!empty($requestData)) {
            foreach ($requestData as $key => $value) {
                if ($value && $key === 'minPrice') {
                    $query->andWhere('p.price >= :minPrice')->setParameter('minPrice', $value);
                } elseif ($value && $key === 'maxPrice') {
                    $query->andWhere('p.price <= :maxPrice')->setParameter('maxPrice', $value);
                } elseif ($value && $key === 'colour') {
                    $query->andWhere('p.colour = :colour')->setParameter('colour', $value);
                } elseif ($value && $key === 'category') {
                    $query->andWhere('p.category = :category')->setParameter('category', $value);
                }
            }
        }

        if (isset($requestData['search'])) {
            $query->andWhere('p.name LIKE :q')
                ->setParameter('q', '%' . $requestData['search'] . '%');
        }


        if (!empty($requestData['sort']) && !empty($requestData['direction'])) {
            $query->orderBy($requestData['sort'], $requestData['direction']);
        }

        return $query;
    }

    public function getProductDistinctColours(): array
    {
        $queryBuilder = $this->createQueryBuilder('p');

        return $queryBuilder->select('p.colour')->distinct()->getQuery()->getResult();
    }
}
