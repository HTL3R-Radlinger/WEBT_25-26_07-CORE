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

    public function findAll(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Erweiterte Suche mit Allergen-Filterung
     *
     * @param string|null $name Namesearch
     * @param array $excludeAllergens Allergen-IDs which are EXCLUDED
     * @param string $sortBy Sort field
     * @param string $order ASC or DESC
     * @param int $page Page number
     * @param int $limit Rows per Page
     * @return Paginator Returns an array of Meal objects
     */
    public function findMeals(
        ?string $name,
        array   $excludeAllergens = [],
        string  $sortBy = 'name',
        string  $order = 'ASC',
        int     $page = 1,
        int     $limit = 10
    ): Paginator
    {
        $qb = $this->createQueryBuilder('m');

        if ($name) {
            $qb->andWhere('m.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        // Allergen-Filter: EXCLUDE Meals with specific Allergenes
        if (!empty($excludeAllergens)) {
            $subQb = $this->createQueryBuilder('m2')
                ->select('m2.id')
                ->innerJoin('m2.allergens', 'a')
                ->where('a.id IN (:excludeAllergens)');

            $qb->andWhere($qb->expr()->notIn('m.id', $subQb->getDQL()))
                ->setParameter('excludeAllergens', $excludeAllergens);
        }

        // Sorting
        $allowedSortFields = ['name', 'id', 'allergens'];
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
