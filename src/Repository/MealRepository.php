<?php

namespace App\Repository;

use App\Entity\Meal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Meal>
 */
class MealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meal::class);
    }

    /**
     * @param string|null $name
     * @param string $sortBy
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return Paginator Returns an array of Meal objects
     */
    public function findMeals(?string $name, string $sortBy = 'name', string $order = 'ASC', int $page = 1, int $limit = 10): Paginator
    {
        $qb = $this->createQueryBuilder('m');

        if ($name) {
            $qb->andWhere('m.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        $allowedSortFields = ['name'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }

        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy('m.' . $sortBy, $order);

        // Pagination
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb, true);
    }
}
