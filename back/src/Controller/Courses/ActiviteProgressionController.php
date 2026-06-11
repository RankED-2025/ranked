<?php

namespace App\Controller\Courses;

use App\Entity\Activite;
use App\Entity\ActiviteProgression;
use App\Entity\Eleve;
use App\Repository\ActiviteProgressionRepository;
use App\Service\ProgressionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/activite-progression', name: 'api_activite_progression_')]
class ActiviteProgressionController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly ActiviteProgressionRepository $activiteProgressionRepository,
        private readonly ProgressionService $progressionService,
    ) {}

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Activite $activite, Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        if (!$user instanceof Eleve) {
            return $this->json(['error' => 'Only students can update their activity progression'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $completed = $data['completed'] ?? null;

        if (!is_bool($completed)) {
            return $this->json(['error' => 'Completed is required'], 400);
        }

        $activiteProgression = $this->activiteProgressionRepository->findOneBy([
            'eleve'    => $user,
            'activite' => $activite,
        ]);

        if ($completed) {
            if (!$activiteProgression instanceof ActiviteProgression) {
                $activiteProgression = new ActiviteProgression();
                $activiteProgression->setEleve($user);
                $activiteProgression->setActivite($activite);
            }

            $activiteProgression->setCompletedAt(new \DateTimeImmutable());
            $this->activiteProgressionRepository->save($activiteProgression, true);
        } elseif ($activiteProgression instanceof ActiviteProgression) {
            $this->activiteProgressionRepository->remove($activiteProgression, true);
        }

        $this->progressionService->updateCourseProgression($user, $activite);

        return $this->json(['message' => 'Activity progression updated successfully'], 200);
    }
}
