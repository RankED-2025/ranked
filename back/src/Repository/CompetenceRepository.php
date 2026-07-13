<?php

namespace App\Repository;

use App\Entity\Competence;
use App\Entity\Eleve;
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

    /**
     * @return array{id: int, nom: string, niveau: string, courseTitle: string, matiere: string, acquired: bool}[]
     */
    public function getStudentCompetencesDetail(Eleve $eleve): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select(
                'c.id',
                'c.nom',
                'c.niveau',
                'co.id as courseId',
                'co.titre as courseTitle',
                'm.libelle as matiere',
                'CASE WHEN ec.id IS NOT NULL THEN 1 ELSE 0 END as acquired',
            )
            ->join('c.cours', 'co')
            ->join('co.matiere', 'm')
            ->join('co.progressions', 'p', 'WITH', 'p.eleve = :eleve')
            ->leftJoin('c.eleveCompetences', 'ec', 'WITH', 'ec.eleve = :eleve')
            ->setParameter('eleve', $eleve)
            ->orderBy('m.libelle', 'ASC')
            ->addOrderBy('co.titre', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(static function (array $row): array {
            $row['acquired'] = (bool) $row['acquired'];
            return $row;
        }, $rows);
    }
}
