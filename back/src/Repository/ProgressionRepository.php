<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\Progression;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Progression>
 */
class ProgressionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Progression::class);
    }

    public function save(Progression $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);

        if ($flush) {
            $em->flush();
        }
    }

    public function remove(Progression $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * @return array{subject: string, average: float}[]
     */
    public function getAverageBySubject(): array
    {
        return $this->createQueryBuilder('p')
            ->select('m.libelle as subject, AVG(p.percentage) as average')
            ->join('p.cours', 'c')
            ->join('c.matiere', 'm')
            ->groupBy('m.id')
            ->orderBy('average', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{type: string, count: int}[]
     */
    public function getBadgeDistribution(): array
    {
        return $this->createQueryBuilder('p')
            ->select('b.type, COUNT(p.id) as count')
            ->join('p.badge', 'b')
            ->groupBy('b.type')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Cours[] $courses
     * @return array{course: Cours, average: float}
     */
    public function getAverageProgressionFromCourses(array $courses): mixed
    {
        $ids = array_map(
            fn(Cours $c) => $c->getId(),
            $courses
        );

        return $this->createQueryBuilder("p")
            ->select("avg(p.percentage)")
            ->where('p.cours IN (:idList)')
            ->setParameter('idList', array_values($ids))
            ->getQuery()
            ->getResult();
    }
}
