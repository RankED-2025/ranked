<?php

namespace App\Repository;

use App\Entity\Eleve;
use App\Entity\Qcm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Qcm>
 */
class QcmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Qcm::class);
    }

    /**
     * Returns QCMs from courses the student is enrolled in, ordered by course then activity order.
     * @return array{label: string, points: int}[]
     */
    public function getForStudentCourses(Eleve $eleve): array
    {
        $rows = $this->createQueryBuilder('q')
            ->select('co.titre as cours, q.gainPts as points, a.ordre')
            ->join('q.activite', 'a')
            ->join('a.cours', 'co')
            ->join('co.progressions', 'p')
            ->where('p.eleve = :eleve')
            ->setParameter('eleve', $eleve)
            ->orderBy('co.id', 'ASC')
            ->addOrderBy('a.ordre', 'ASC')
            ->getQuery()
            ->getResult();

        $counters = [];
        return array_map(function (array $row) use (&$counters) {
            $counters[$row['cours']] = ($counters[$row['cours']] ?? 0) + 1;
            return [
                'label' => $row['cours'] . ' – Q' . $counters[$row['cours']],
                'points' => (int) $row['points'],
            ];
        }, $rows);
    }
}
