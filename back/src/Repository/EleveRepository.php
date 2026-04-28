<?php

namespace App\Repository;

use App\Entity\Eleve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Eleve>
 */
class EleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Eleve::class);
    }

    /**
     * @return array{classe: string, count: int}[]
     */
    public function getActiveStudentsPerClass(): array
    {
        return $this->createQueryBuilder('e')
            ->select('cl.nom as classe, COUNT(DISTINCT e.id) as count')
            ->join('e.classe', 'cl')
            ->join('e.progressions', 'p')
            ->groupBy('cl.id')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
