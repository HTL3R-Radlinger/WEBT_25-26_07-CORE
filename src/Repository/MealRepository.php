<?php

namespace App\Repository;

use App\Entity\Meal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * MealRepository - Custom database queries for Meal entity
 *
 * This repository extends ServiceEntityRepository which provides basic
 * CRUD methods. We add custom methods for complex queries.
 *
 * @extends ServiceEntityRepository<Meal>
 */
class MealRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * Required by Symfony to register this repository
     *
     * @param ManagerRegistry $registry Doctrine's service for managing entities
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meal::class);
    }

    /**
     * Override the Default findAll Method
     *
     * Standard findAll() returns meals in random order.
     * This version returns meals ordered by ID (ascending).
     *
     * @return array Array of Meal objects ordered by ID
     */
    public function findAll(): array
    {
        // createQueryBuilder('m') creates a query with 'm' as alias for Meal
        return $this->createQueryBuilder('m')
            ->orderBy('m.id', 'ASC')  // Order by ID ascending
            ->getQuery()               // Convert to Query object
            ->getResult();             // Execute and return results as array
    }
    /**
     * DQL Variant
     * public function findAll(): array
     * {
     *      return $this->getEntityManager()
     *      ->createQuery(
     *      'SELECT m
     *      FROM App\Entity\Meal m
     *      ORDER BY m.id ASC'
     *      )
     *      ->getResult();
     * }
     */

    /**
     * FetchAssociative
     *
     * public function findMealById(int $id, EntityManagerInterface $em)
     * {
     *      $conn = $em->getConnection();
     *
     *      $sql = "SELECT id, name, price FROM meal WHERE id = :id";
     *
     *      $result = $conn->executeQuery($sql, [
     *          'id' => $id
     *      ]);
     *
     *      return $result->fetchAssociative();
     * }
     *
     * Output:
     * [
     *  "id" => 1,
     *  "name" => "Pizza",
     *  "price" => 9.90
     * ]
     */

    /**
     * public function countMeals(EntityManagerInterface $em)
     * {
     *      $conn = $em->getConnection();
     *      $sql = "SELECT COUNT(*) FROM meal";
     *      return $conn->executeQuery($sql)->fetchOne();
     * }
     *
     * Output:
     * 12
     */

    /**
     * public function findFirstMeal(EntityManagerInterface $em)
     * {
     *      $conn = $em->getConnection();
     *      $sql = "SELECT id, name, price FROM meal LIMIT 1";
     *      return $conn->executeQuery($sql)->fetchNumeric();
     * }
     *
     * Output:
     * [
     *  0 => 1,
     *  1 => "Pizza",
     *  2 => 9.9
     * ]
     */


    /**
     * Advanced Meal Search with Filtering, Sorting, and Pagination
     *
     * This is a complex method that handles:
     * 1. Search by meal name (partial match)
     * 2. Exclude meals containing specific allergens
     * 3. Sort by different fields
     * 4. Paginate results
     *
     * Example usage:
     * findMeals('Pizza', [1, 3], 'name', 'DESC', 2, 10)
     * -> Find meals with "Pizza" in name, excluding allergens 1 and 3,
     *    sorted by name descending, page 2, 10 items per page
     *
     * @param string|null $name Search term for meal name (partial match)
     * @param array $excludeAllergens Array of allergen IDs to exclude
     * @param string $sortBy Field to sort by ('name', 'id', or 'allergens')
     * @param string $order Sort order ('ASC' or 'DESC')
     * @param int $page Page number (1-based)
     * @param int $limit Number of items per page
     * @return Paginator Paginator object containing results and total count
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
        // Create query builder with 'm' as alias for Meal
        $qb = $this->createQueryBuilder('m');

        // --- FILTER 1: Search by Name ---
        // If name parameter is provided, add LIKE condition
        if ($name) {
            // LIKE '%searchterm%' finds partial matches
            $qb->andWhere('m.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        // --- FILTER 2: Exclude Allergens ---
        // Complex filter: Exclude meals that contain any of the specified allergens
        if (!empty($excludeAllergens)) {
            // Step 1: Create a subquery to find meal IDs that have the excluded allergens
            $subQb = $this->createQueryBuilder('m2')  // 'm2' is different alias to avoid conflicts
            ->select('m2.id')                      // Select only the ID
            ->innerJoin('m2.allergens', 'a')       // Join with allergens table
            ->where('a.id IN (:excludeAllergens)'); // Where allergen ID is in the excluded list

            // Step 2: Exclude those meal IDs from main query
            // NOT IN (subquery) means: don't include meals from the subquery result
            $qb->andWhere($qb->expr()->notIn('m.id', $subQb->getDQL()))
                ->setParameter('excludeAllergens', $excludeAllergens);

            // How this works:
            // Subquery finds: IDs of meals that HAVE allergen 1 or 3
            // Main query excludes: Those IDs
            // Result: Only meals that DON'T have allergen 1 or 3
        }

        // --- SORTING ---
        // Validate sortBy parameter (prevent SQL injection)
        $allowedSortFields = ['name', 'id', 'allergens'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';  // Default to 'name' if invalid value provided
        }

        // Validate order parameter (only ASC or DESC allowed)
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        // Add ORDER BY clause to query
        $qb->orderBy('m.' . $sortBy, $order);

        // --- PAGINATION ---
        // setFirstResult: Calculate offset (skip first N results)
        // Example: Page 2 with limit 10 -> skip first 10 results (page 1)
        // Formula: (page - 1) * limit
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);  // Limit number of results

        // Return Paginator instead of array
        // Paginator provides both results and total count for pagination
        // Second parameter 'true' means fetch join (optimize queries)
        return new Paginator($qb, true);
    }
}
