<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\Eleve;
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
     * @return array{title: string, percentage: int}[]
     */
    public function getStudentProgressions(Eleve $eleve): array
    {
        return $this->createQueryBuilder('p')
            ->select('co.titre as title, p.percentage')
            ->join('p.cours', 'co')
            ->where('p.eleve = :eleve')
            ->setParameter('eleve', $eleve)
            ->orderBy('p.percentage', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{type: string, count: int}[]
     */
    public function getStudentBadgeDistribution(Eleve $eleve): array
    {
        return $this->createQueryBuilder('p')
            ->select('b.type, COUNT(p.id) as count')
            ->join('p.badge', 'b')
            ->where('p.eleve = :eleve')
            ->setParameter('eleve', $eleve)
            ->groupBy('b.type')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{eleveId: int, average: float}[]
     */
    public function getClassAverages(Eleve $eleve): array
    {
        if (!$eleve->getClasse()) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->select('IDENTITY(p.eleve) as eleveId, AVG(p.percentage) as average')
            ->join('p.eleve', 'e')
            ->where('e.classe = :classe')
            ->setParameter('classe', $eleve->getClasse())
            ->groupBy('p.eleve')
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
     * @return array{eleveId: int, name: string, firstname: string, classe: string|null, average: float}[]
     */
    public function getBestStudents(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->select('IDENTITY(p.eleve) as eleveId, e.name, e.firstname, cl.nom as classe, AVG(p.percentage) as average')
            ->join('p.eleve', 'e')
            ->leftJoin('e.classe', 'cl')
            ->groupBy('p.eleve')
            ->orderBy('average', 'DESC')
            ->setMaxResults($limit)
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
