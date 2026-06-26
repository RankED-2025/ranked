<?php

namespace App\Controller\Stats;

use App\Entity\Eleve;
use App\Repository\CompetenceRepository;
use App\Repository\EleveCompetenceRepository;
use App\Repository\ProgressionRepository;
use App\Repository\QcmRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/my-stats', name: 'api_my_stats_')]
class PersonalStatsController extends AbstractController
{
    public function __construct(
        private readonly Security                 $security,
        private readonly ProgressionRepository    $progressionRepository,
        private readonly EleveCompetenceRepository $eleveCompetenceRepository,
        private readonly CompetenceRepository     $competenceRepository,
        private readonly QcmRepository            $qcmRepository,
    ) {}

    private function getEleve(): Eleve|JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof Eleve) {
            return $this->json(['error' => 'Student account required'], 403);
        }
        return $user;
    }

    #[Route('/progressions', name: 'progressions', methods: ['GET'])]
    public function progressions(): JsonResponse
    {
        $eleve = $this->getEleve();
        if ($eleve instanceof JsonResponse) return $eleve;

        return $this->json($this->progressionRepository->getStudentProgressions($eleve));
    }

    #[Route('/competences', name: 'competences', methods: ['GET'])]
    public function competences(): JsonResponse
    {
        $eleve = $this->getEleve();
        if ($eleve instanceof JsonResponse) return $eleve;

        $totals = [];
        foreach ($this->competenceRepository->getTotalByMatiere() as $row) {
            $totals[(int) $row['matiereId']] = ['matiere' => $row['matiere'], 'total' => (int) $row['total']];
        }

        $acquired = [];
        foreach ($this->eleveCompetenceRepository->getAcquiredByMatiere($eleve) as $row) {
            $acquired[(int) $row['matiereId']] = (int) $row['acquired'];
        }

        $data = array_map(function (int $matiereId, array $info) use ($acquired) {
            $count = $acquired[$matiereId] ?? 0;
            return [
                'matiere'    => $info['matiere'],
                'percentage' => $info['total'] > 0 ? round($count / $info['total'] * 100, 1) : 0,
            ];
        }, array_keys($totals), array_values($totals));

        return $this->json(array_values($data));
    }

    #[Route('/quiz-scores', name: 'quiz_scores', methods: ['GET'])]
    public function quizScores(): JsonResponse
    {
        $eleve = $this->getEleve();
        if ($eleve instanceof JsonResponse) return $eleve;

        return $this->json($this->qcmRepository->getForStudentCourses($eleve));
    }

    #[Route('/badges', name: 'badges', methods: ['GET'])]
    public function badges(): JsonResponse
    {
        $eleve = $this->getEleve();
        if ($eleve instanceof JsonResponse) return $eleve;

        return $this->json($this->progressionRepository->getStudentBadgeDistribution($eleve));
    }

    #[Route('/class-rank', name: 'class_rank', methods: ['GET'])]
    public function classRank(): JsonResponse
    {
        $eleve = $this->getEleve();
        if ($eleve instanceof JsonResponse) return $eleve;

        $classData = $this->progressionRepository->getClassAverages($eleve);

        if (empty($classData)) {
            return $this->json(['error' => 'No class or progression data available'], 404);
        }

        $myId = $eleve->getId();
        $myAverage = 0.0;

        foreach ($classData as $row) {
            if ((int) $row['eleveId'] === $myId) {
                $myAverage = (float) $row['average'];
                break;
            }
        }

        $rank = 1;
        foreach ($classData as $row) {
            if ((float) $row['average'] > $myAverage) {
                $rank++;
            }
        }

        $total = count($classData);
        $percentile = $total > 1 ? round(($total - $rank) / ($total - 1) * 100, 1) : 100.0;

        return $this->json([
            'myAverage'  => round($myAverage, 1),
            'rank'       => $rank,
            'total'      => $total,
            'percentile' => $percentile,
        ]);
    }
}
