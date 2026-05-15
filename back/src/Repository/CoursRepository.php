<?php

namespace App\Repository;

use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    /**
     * @param int $limit
     * @return Cours[]
     */
    public function getTopCourses(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('AVG(p.percentage) AS HIDDEN avg_percentage')
            ->leftJoin('c.progressions', 'p')
            ->groupBy('c.id')
            ->orderBy('avg_percentage', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
