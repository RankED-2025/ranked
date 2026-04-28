<?php

namespace App\Repository;

use App\Entity\Competence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competence>
 */
class CompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competence::class);
    }

    /**
     * @return array{matiereId: int, matiere: string, total: int}[]
     */
    public function getTotalByMatiere(): array
    {
        return $this->createQueryBuilder('c')
            ->select('m.id as matiereId, m.libelle as matiere, COUNT(c.id) as total')
            ->join('c.cours', 'co')
            ->join('co.matiere', 'm')
            ->groupBy('m.id')
            ->getQuery()
            ->getResult();
    }
}
