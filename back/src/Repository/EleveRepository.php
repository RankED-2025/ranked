<?php

namespace App\Repository;

use App\Entity\Eleve;
use App\Entity\Professeur;
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
    public function getActiveStudentsPerClass(Professeur $professeur): array
    {
        return $this->createQueryBuilder('e')
            ->select('cl.nom as classe, COUNT(DISTINCT e.id) as count')
            ->join('e.classe', 'cl')
            ->join('e.progressions', 'p')
            ->where('cl.professeur = :professeur')
            ->setParameter('professeur', $professeur)
            ->groupBy('cl.id')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{week: string, count: int}[]
     */
    public function getRegistrationsPerWeek(Professeur $professeur, int $weeks = 8): array
    {
        $start = new \DateTimeImmutable("monday -{$weeks} weeks");

        /** @var array<array{createdAt: \DateTimeImmutable}> $students */
        $students = $this->createQueryBuilder('e')
            ->select('e.createdAt')
            ->join('e.classe', 'cl')
            ->where('cl.professeur = :professeur')
            ->andWhere('e.createdAt >= :start')
            ->setParameter('professeur', $professeur)
            ->setParameter('start', $start)
            ->getQuery()
            ->getResult();

        $byWeek = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $key = (new \DateTimeImmutable("monday -{$i} weeks"))->format('Y-\WW');
            $byWeek[$key] = 0;
        }

        foreach ($students as $row) {
            $key = $row['createdAt']->format('Y-\WW');
            if (array_key_exists($key, $byWeek)) {
                $byWeek[$key]++;
            }
        }

        return array_map(
            fn(string $week, int $count) => ['week' => $week, 'count' => $count],
            array_keys($byWeek),
            array_values($byWeek),
        );
    }
}
