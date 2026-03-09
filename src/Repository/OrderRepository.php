<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * OrderRepository - Custom database queries for Order entity
 *
 * This repository provides methods for:
 * - Finding orders with filters and pagination
 * - Getting order statistics by status
 *
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param ManagerRegistry $registry Doctrine's entity manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Find Orders with Filtering, Sorting, and Pagination
     *
     * This method handles complex order queries with multiple filters:
     * 1. Filter by status (pending, confirmed, etc.)
     * 2. Filter by buyer ID (specific customer)
     * 3. Sort by different fields (date, price, status)
     * 4. Paginate results
     *
     * Additionally, this method uses JOIN FETCH to avoid N+1 query problems
     * by loading related entities (buyer, meals) in the same query.
     *
     * Example usage:
     * findByStatus('pending', 5, 'date', 'DESC', 1, 10)
     * -> Find pending orders for buyer #5, sorted by date descending,
     *    page 1, 10 items per page
     *
     * @param string|null $status Filter by order status (e.g., 'pending')
     * @param int|null $buyerId Filter by buyer's user ID
     * @param string $sortBy Field to sort by ('date', 'price', 'status')
     * @param string $order Sort order ('ASC' or 'DESC')
     * @param int $page Page number (1-based)
     * @param int $limit Number of items per page
     * @return Paginator Paginator object with results and total count
     */
    public function findByStatus(
        ?string $status = null,
        ?int    $buyerId = null,
        string  $sortBy = 'date',
        string  $order = 'DESC',
        int     $page = 1,
        int     $limit = 10
    ): Paginator
    {
        // Create query builder with 'o' as alias for Order
        $qb = $this->createQueryBuilder('o');

        // --- EAGER LOADING: Prevent N+1 Query Problem ---
        // Without these JOINs, Doctrine would execute:
        // - 1 query to get orders
        // - N queries to get buyer for each order
        // - M queries to get meals for each order
        // Total: 1 + N + M queries (very slow!)

        // Join with buyer (User entity) and fetch it immediately
        // 'u' is the alias for User
        $qb->leftJoin('o.buyer', 'u')
            ->addSelect('u');  // Fetch user data in same query

        // Join with meals and fetch them immediately
        // 'm' is the alias for Meal
        $qb->leftJoin('o.Meals', 'm')
            ->addSelect('m');  // Fetch meal data in same query

        // Now all data is loaded in just 1 query (much faster!)

        // --- FILTER 1: Filter by Status ---
        if ($status) {
            // Only include orders with matching status
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        // --- FILTER 2: Filter by Buyer ID ---
        if ($buyerId) {
            // Only include orders from specific buyer
            // Note: We use 'u.id' because we joined the buyer as 'u'
            $qb->andWhere('u.id = :buyerId')
                ->setParameter('buyerId', $buyerId);
        }

        // --- SORTING ---
        // Validate sortBy field (prevent SQL injection)
        $allowedSortFields = ['date', 'price', 'status'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date';  // Default to 'date' if invalid
        }

        // Validate order direction (only ASC or DESC)
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        // Add ORDER BY clause
        $qb->orderBy('o.' . $sortBy, $order);

        // --- PAGINATION ---
        // Calculate offset: skip results from previous pages
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);  // Limit results to page size

        // Return Paginator for both results and total count
        // 'true' parameter enables fetch join optimization
        return new Paginator($qb, true);
    }

    /**
     * Count Orders by Status - Statistics Query
     *
     * Returns the number of orders in each status.
     * Useful for dashboard displays showing:
     * - 5 pending orders
     * - 12 confirmed orders
     * - 8 preparing orders
     * - etc.
     *
     * Result format:
     * [
     *   ['status' => 'pending', 'total' => 5],
     *   ['status' => 'confirmed', 'total' => 12],
     *   ['status' => 'preparing', 'total' => 8],
     *   ...
     * ]
     *
     * @return array Array of associative arrays with status and total
     */
    public function countByStatus(): array
    {
        return $this->createQueryBuilder('o')
            // SELECT two fields:
            // 1. o.status: The status value
            // 2. COUNT(o.id): Number of orders with that status (aliased as 'total')
            ->select('o.status, COUNT(o.id) as total')

            // GROUP BY: Combine rows with same status
            // This is what creates separate counts for each status
            ->groupBy('o.status')
            ->getQuery()     // Convert to Query object
            ->getResult();   // Execute and return array

        // SQL equivalent:
        // SELECT status, COUNT(id) as total
        // FROM `order`
        // GROUP BY status
    }
}
