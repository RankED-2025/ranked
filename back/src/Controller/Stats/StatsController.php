<?php

namespace App\Controller\Stats;

use App\Entity\Classe;
use App\Repository\ClasseRepository;
use App\Repository\EleveRepository;
use App\Repository\ProgressionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/stats', name: 'api_stats_')]
class StatsController extends AbstractController
{
    public function __construct(
        private readonly ProgressionRepository $progressionRepository,
        private readonly EleveRepository       $eleveRepository,
        private readonly UserRepository        $userRepository,
        private readonly ClasseRepository      $classeRepository,
    ) {}

    #[Route('/completion-by-subject', name: 'completion_by_subject', methods: ['GET'])]
    public function completionBySubject(): JsonResponse
    {
        $rows = $this->progressionRepository->getAverageBySubject();

        $data = array_map(fn(array $row) => [
            'subject' => $row['subject'],
            'average' => round((float) $row['average'], 2),
        ], $rows);

        return $this->json($data);
    }

    #[Route('/active-students-per-class', name: 'active_students_per_class', methods: ['GET'])]
    public function activeStudentsPerClass(): JsonResponse
    {
        $rows = $this->eleveRepository->getActiveStudentsPerClass();

        $data = array_map(fn(array $row) => [
            'classe' => $row['classe'],
            'count'  => (int) $row['count'],
        ], $rows);

        return $this->json($data);
    }

    #[Route('/badge-distribution', name: 'badge_distribution', methods: ['GET'])]
    public function badgeDistribution(): JsonResponse
    {
        $rows = $this->progressionRepository->getBadgeDistribution();

        $data = array_map(fn(array $row) => [
            'type'  => $row['type'],
            'count' => (int) $row['count'],
        ], $rows);

        return $this->json($data);
    }

    #[Route('/registrations', name: 'registrations', methods: ['GET'])]
    public function registrations(): JsonResponse
    {
        return $this->json($this->userRepository->getRegistrationsPerWeek(8));
    }

    #[Route('/best-students/{classe}/{limit}', name: 'best_students', requirements: ['limit' => '[1-9]\d*'], methods: ['GET'])]
    public function bestStudents(Classe $classe, int $limit = 5): JsonResponse
    {
        $rows = $this->progressionRepository->getBestStudents($limit, $classe);

        $data = [];
        foreach ($rows as $i => $row) {
            $data[] = [
                'rank'      => $i + 1,
                'name'      => $row['name'],
                'firstname' => $row['firstname'],
                'average'   => round((float) $row['average'], 1),
            ];
        }

        return $this->json($data);
    }
}
