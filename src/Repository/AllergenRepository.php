<?php

namespace App\Repository;

use App\Entity\Allergen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * AllergenRepository - Database queries for Allergen entity
 *
 * This repository currently only uses the default methods provided
 * by ServiceEntityRepository (findAll, find, findBy, etc.)
 *
 * The commented-out examples show how to add custom query methods
 * if needed in the future.
 *
 * @extends ServiceEntityRepository<Allergen>
 */
class AllergenRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param ManagerRegistry $registry Doctrine's entity manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Allergen::class);
    }
}
