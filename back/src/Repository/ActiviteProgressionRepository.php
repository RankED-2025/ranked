<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\ActiviteProgression;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActiviteProgression>
 */
class ActiviteProgressionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActiviteProgression::class);
    }

    public function save(ActiviteProgression $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);

        if ($flush) {
            $em->flush();
        }
    }

    public function remove(ActiviteProgression $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * @return int[]
     */
    public function findCompletedActiviteIds(Eleve $eleve, Cours $cours): array
    {
        $result = $this->createQueryBuilder('ap')
            ->select('IDENTITY(ap.activite) as activiteId')
            ->join('ap.activite', 'a')
            ->where('ap.eleve = :eleve')
            ->andWhere('a.cours = :cours')
            ->andWhere('ap.completedAt IS NOT NULL')
            ->setParameter('eleve', $eleve)
            ->setParameter('cours', $cours)
            ->getQuery()
            ->getResult();

        return array_map(fn(array $row) => (int) $row['activiteId'], $result);
    }
}
