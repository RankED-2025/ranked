<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return array{week: string, count: int}[]
     */
    public function getRegistrationsPerWeek(int $weeks = 8): array
    {
        $start = new \DateTimeImmutable("monday -{$weeks} weeks");

        /** @var array<array{createdAt: \DateTimeImmutable}> $users */
        $users = $this->createQueryBuilder('u')
            ->select('u.createdAt')
            ->where('u.createdAt >= :start')
            ->setParameter('start', $start)
            ->getQuery()
            ->getResult();

        $byWeek = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $key = (new \DateTimeImmutable("monday -{$i} weeks"))->format('Y-\WW');
            $byWeek[$key] = 0;
        }

        foreach ($users as $row) {
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

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
