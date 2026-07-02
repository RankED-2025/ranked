<?php

namespace App\Repository;

use App\Entity\Classe;
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
    private const ELEVE_CONDITION = 'p.eleve = :eleve';

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
            ->where(self::ELEVE_CONDITION)
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
            ->where(self::ELEVE_CONDITION)
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
     * @return array{eleveId: int, name: string, firstname: string, average: float, totalCourses: int, completedCourses: int}[]
     */
    public function getBestStudents(int $limit, Classe $classe): array
    {
        return $this->createQueryBuilder('p')
            ->select(
                'IDENTITY(p.eleve) as eleveId',
                'e.name',
                'e.firstname',
                'AVG(p.percentage) as average',
                'COUNT(p.id) as totalCourses',
                'SUM(CASE WHEN p.percentage = 100 THEN 1 ELSE 0 END) as completedCourses',
            )
            ->join('p.eleve', 'e')
            ->where('e.classe = :classe')
            ->setParameter('classe', $classe)
            ->groupBy('p.eleve')
            ->orderBy('average', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the best-performing subject (by average) for each student, ordered by subject average descending.
     * The first entry per eleveId is the top subject for that student.
     *
     * @param int[] $eleveIds
     * @return array{eleveId: int, subject: string, subjectAverage: float}[]
     */
    public function getBestStudentTopSubjects(array $eleveIds): array
    {
        if (empty($eleveIds)) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->select(
                'IDENTITY(p.eleve) as eleveId',
                'm.libelle as subject',
                'AVG(p.percentage) as subjectAverage',
            )
            ->join('p.eleve', 'e')
            ->join('p.cours', 'c')
            ->join('c.matiere', 'm')
            ->where('e.id IN (:eleveIds)')
            ->setParameter('eleveIds', $eleveIds)
            ->groupBy('p.eleve, m.id')
            ->orderBy('subjectAverage', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{courseId: int, courseTitle: string, badgeType: string, badgeLabel: string, percentage: int}[]
     */
    public function getStudentBadgesDetail(Eleve $eleve): array
    {
        return $this->createQueryBuilder('p')
            ->select(
                'IDENTITY(p.cours) as courseId',
                'co.titre as courseTitle',
                'b.type as badgeType',
                'b.label as badgeLabel',
                'p.percentage',
            )
            ->join('p.cours', 'co')
            ->join('p.badge', 'b')
            ->where(self::ELEVE_CONDITION)
            ->setParameter('eleve', $eleve)
            ->orderBy('p.percentage', 'DESC')
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
