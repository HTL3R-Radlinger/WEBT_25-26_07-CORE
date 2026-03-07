<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Order> */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Bestellungen nach Status filtern (mit Pagination)
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
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.buyer', 'u')
            ->addSelect('u')
            ->leftJoin('o.Meals', 'm')
            ->addSelect('m');

        if ($status) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        if ($buyerId) {
            $qb->andWhere('u.id = :buyerId')
                ->setParameter('buyerId', $buyerId);
        }

        $allowedSortFields = ['date', 'price', 'status'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date';
        }

        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy('o.' . $sortBy, $order);

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb, true);
    }

    /**
     * Statistik: Anzahl Bestellungen pro Status
     */
    public function countByStatus(): array
    {
        return $this->createQueryBuilder('o')
            ->select('o.status, COUNT(o.id) as total')
            ->groupBy('o.status')
            ->getQuery()
            ->getResult();
    }
}
