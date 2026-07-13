<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\Professeur;
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
     * @param Professeur|null $professeur When given, restricts the results to this professor's own courses
     * (used by the professor stats page); left null for the public top-courses catalog.
     * @return Cours[]
     */
    public function getTopCourses(int $limit = 5, ?Professeur $professeur = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('AVG(p.percentage) AS HIDDEN avg_percentage')
            ->leftJoin('c.progressions', 'p')
            ->groupBy('c.id')
            ->orderBy('avg_percentage', 'DESC')
            ->setMaxResults($limit);

        if ($professeur !== null) {
            $qb->andWhere('c.professeur = :professeur')
                ->setParameter('professeur', $professeur);
        }

        return $qb->getQuery()->getResult();
    }
}
