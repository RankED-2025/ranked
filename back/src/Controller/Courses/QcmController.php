<?php

namespace App\Controller\Courses;

use App\Entity\Activite;
use App\Entity\ActiviteProgression;
use App\Entity\Eleve;
use App\Repository\ActiviteProgressionRepository;
use App\Repository\ProgressionRepository;
use App\Service\ProgressionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/qcm', name: 'api_qcm_')]
class QcmController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly ActiviteProgressionRepository $activiteProgressionRepository,
        private readonly ProgressionRepository $progressionRepository,
        private readonly ProgressionService $progressionService,
    ) {}

    /**
     * Returns the quiz for a student to take. Correct answers are never exposed.
     * If the student already attempted it, the locked result is returned instead.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Activite $activite): JsonResponse
    {
        $context = $this->resolveContext($activite);
        if ($context instanceof JsonResponse) {
            return $context;
        }

        [$eleve, $qcm] = $context;

        $existing = $this->activiteProgressionRepository->findOneBy([
            'eleve'    => $eleve,
            'activite' => $activite,
        ]);

        if ($existing instanceof ActiviteProgression) {
            return $this->json([
                'id'       => $qcm->getId(),
                'gainPts'  => $qcm->getGainPts(),
                'locked'   => true,
                'result'   => [
                    'score'     => $existing->getScore(),
                    'total'     => $existing->getTotal(),
                    'earnedPts' => $existing->getEarnedPts(),
                ],
            ]);
        }

        return $this->json([
            'id'       => $qcm->getId(),
            'gainPts'  => $qcm->getGainPts(),
            'locked'   => false,
            'questions' => array_map(fn($question) => [
                'id'       => $question->getId(),
                'enonce'   => $question->getEnonce(),
                'reponses' => array_map(fn($reponse) => [
                    'id'    => $reponse->getId(),
                    'texte' => $reponse->getTexte(),
                ], $question->getReponses()->toArray()),
            ], $qcm->getQuestions()->toArray()),
        ]);
    }

    /**
     * Grades a student's answers, awards proportional points and records the single attempt.
     */
    #[Route('/{id}/submit', name: 'submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function submit(Activite $activite, Request $request): JsonResponse
    {
        $context = $this->resolveContext($activite);
        if ($context instanceof JsonResponse) {
            return $context;
        }

        [$eleve, $qcm] = $context;

        $existing = $this->activiteProgressionRepository->findOneBy([
            'eleve'    => $eleve,
            'activite' => $activite,
        ]);

        if ($existing instanceof ActiviteProgression) {
            return $this->json(['error' => 'This quiz has already been submitted'], 409);
        }

        $payload = json_decode($request->getContent(), true);
        $answers = is_array($payload['answers'] ?? null) ? $payload['answers'] : null;

        if ($answers === null) {
            return $this->json(['error' => 'Answers are required'], 400);
        }

        $questions = $qcm->getQuestions()->toArray();
        $total = count($questions);

        if ($total === 0) {
            return $this->json(['error' => 'This quiz has no questions'], 400);
        }

        $score = 0;
        foreach ($questions as $question) {
            $questionId = (string) $question->getId();

            if (!array_key_exists($questionId, $answers)) {
                return $this->json(['error' => 'All questions must be answered'], 400);
            }

            $selectedId = (int) $answers[$questionId];
            $matched = false;

            foreach ($question->getReponses() as $reponse) {
                if ($reponse->getId() === $selectedId) {
                    $matched = true;
                    if ($reponse->isCorrect()) {
                        $score++;
                    }
                    break;
                }
            }

            if (!$matched) {
                return $this->json(['error' => 'Invalid answer for one of the questions'], 400);
            }
        }

        $earnedPts = (int) round(($qcm->getGainPts() ?? 0) * $score / $total);

        $activiteProgression = new ActiviteProgression();
        $activiteProgression->setEleve($eleve);
        $activiteProgression->setActivite($activite);
        $activiteProgression->setCompletedAt(new \DateTimeImmutable());
        $activiteProgression->setScore($score);
        $activiteProgression->setTotal($total);
        $activiteProgression->setEarnedPts($earnedPts);
        $this->activiteProgressionRepository->save($activiteProgression, true);

        $this->progressionService->updateCourseProgression($eleve, $activite);

        return $this->json([
            'score'     => $score,
            'total'     => $total,
            'earnedPts' => $earnedPts,
            'gainPts'   => $qcm->getGainPts(),
        ]);
    }

    /**
     * Shared guards: student role, QCM activity, and course enrollment.
     *
     * @return JsonResponse|array{0: Eleve, 1: \App\Entity\Qcm}
     */
    private function resolveContext(Activite $activite): JsonResponse|array
    {
        $user = $this->security->getUser();

        if (!$user instanceof Eleve) {
            return $this->json(['error' => 'Only students can take quizzes'], 403);
        }

        $qcm = $activite->getQcm();

        if ($qcm === null) {
            return $this->json(['error' => 'This activity is not a quiz'], 404);
        }

        $cours = $activite->getCours();

        if (!$cours) {
            return $this->json(['error' => 'Activity does not belong to any course'], 403);
        }

        $progression = $this->progressionRepository->findOneBy([
            'eleve' => $user,
            'cours' => $cours,
        ]);

        if (!$progression) {
            return $this->json(['error' => 'Student is not enrolled in this course'], 403);
        }

        return [$user, $qcm];
    }
}
