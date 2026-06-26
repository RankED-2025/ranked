<?php

namespace App\Repository;

use App\Entity\Eleve;
use App\Entity\EleveCompetence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EleveCompetence>
 */
class EleveCompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EleveCompetence::class);
    }

    /**
     * @return array{matiereId: int, matiere: string, acquired: int}[]
     */
    public function getAcquiredByMatiere(Eleve $eleve): array
    {
        return $this->createQueryBuilder('ec')
            ->select('m.id as matiereId, m.libelle as matiere, COUNT(ec.id) as acquired')
            ->join('ec.competence', 'c')
            ->join('c.cours', 'co')
            ->join('co.matiere', 'm')
            ->where('ec.eleve = :eleve')
            ->setParameter('eleve', $eleve)
            ->groupBy('m.id')
            ->getQuery()
            ->getResult();
    }
}
