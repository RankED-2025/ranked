<?php

namespace App\Controller\Stats;

use App\Entity\Classe;
use App\Entity\Professeur;
use App\Repository\ClasseRepository;
use App\Repository\EleveRepository;
use App\Repository\ProgressionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/stats', name: 'api_stats_')]
class StatsController extends AbstractController
{
    public function __construct(
        private readonly ProgressionRepository $progressionRepository,
        private readonly EleveRepository       $eleveRepository,
        private readonly Security              $security,
    ) {}

    private function requireProfessor(): Professeur|JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof Professeur) {
            return $this->json(['error' => 'Only professors can access this resource'], 403);
        }

        return $user;
    }

    #[Route('/completion-by-subject', name: 'completion_by_subject', methods: ['GET'])]
    public function completionBySubject(): JsonResponse
    {
        $professeur = $this->requireProfessor();
        if ($professeur instanceof JsonResponse) return $professeur;

        $rows = $this->progressionRepository->getAverageBySubject($professeur);

        $data = array_map(fn(array $row) => [
            'subject' => $row['subject'],
            'average' => round((float) $row['average'], 2),
        ], $rows);

        return $this->json($data);
    }

    #[Route('/active-students-per-class', name: 'active_students_per_class', methods: ['GET'])]
    public function activeStudentsPerClass(): JsonResponse
    {
        $professeur = $this->requireProfessor();
        if ($professeur instanceof JsonResponse) return $professeur;

        $rows = $this->eleveRepository->getActiveStudentsPerClass($professeur);

        $data = array_map(fn(array $row) => [
            'classe' => $row['classe'],
            'count'  => (int) $row['count'],
        ], $rows);

        return $this->json($data);
    }

    #[Route('/badge-distribution', name: 'badge_distribution', methods: ['GET'])]
    public function badgeDistribution(): JsonResponse
    {
        $professeur = $this->requireProfessor();
        if ($professeur instanceof JsonResponse) return $professeur;

        $rows = $this->progressionRepository->getBadgeDistribution($professeur);

        $data = array_map(fn(array $row) => [
            'type'  => $row['type'],
            'count' => (int) $row['count'],
        ], $rows);

        return $this->json($data);
    }

    #[Route('/registrations', name: 'registrations', methods: ['GET'])]
    public function registrations(): JsonResponse
    {
        $professeur = $this->requireProfessor();
        if ($professeur instanceof JsonResponse) return $professeur;

        return $this->json($this->eleveRepository->getRegistrationsPerWeek($professeur, 8));
    }

    #[Route('/best-students/{classe}/{limit}', name: 'best_students', requirements: ['limit' => '[1-9]\d*'], methods: ['GET'])]
    public function bestStudents(Classe $classe, int $limit = 5): JsonResponse
    {
        $user = $this->requireProfessor();
        if ($user instanceof JsonResponse) return $user;

        if ($classe->getProfesseur()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'You are not the professor of this class'], 403);
        }

        $rows = $this->progressionRepository->getBestStudents($limit, $classe, $user);

        $eleveIds = array_map(fn(array $row) => (int) $row['eleveId'], $rows);
        $topSubjectsRaw = $this->progressionRepository->getBestStudentTopSubjects($eleveIds, $classe, $user);

        $topSubjectMap = [];
        foreach ($topSubjectsRaw as $entry) {
            $id = (int) $entry['eleveId'];
            if (!isset($topSubjectMap[$id])) {
                $topSubjectMap[$id] = $entry['subject'];
            }
        }

        $data = [];
        foreach ($rows as $i => $row) {
            $eleveId = (int) $row['eleveId'];
            $data[] = [
                'rank'             => $i + 1,
                'name'             => $row['name'],
                'firstname'        => $row['firstname'],
                'average'          => round((float) $row['average'], 1),
                'completedCourses' => (int) $row['completedCourses'],
                'totalCourses'     => (int) $row['totalCourses'],
                'topSubject'       => $topSubjectMap[$eleveId] ?? null,
            ];
        }

        return $this->json($data);
    }
}
