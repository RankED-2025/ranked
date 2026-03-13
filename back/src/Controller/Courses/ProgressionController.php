<?php

namespace App\Controller\Courses;

use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\Progression;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\CoursRepository;
use App\Repository\ProgressionRepository;

#[Route('/api/progression', name: 'api_progression_')]
class ProgressionController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly ProgressionRepository $progressionRepository,
        private readonly CoursRepository $coursRepository,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        if (!$user instanceof Eleve) {
            return $this->json(['error' => 'Only students have progressions'], 403);
        }

        $progressions = $this->progressionRepository->findBy(['eleve' => $user]);

        $data = array_map(function (Progression $progression) {
            $cours = $progression->getCours();
            $badge = $progression->getBadge();

            return [
                'percentage' => $progression->getPercentage(),
                'cours' => $cours ? [
                    'id' => $cours->getId(),
                    'professeur' => $cours->getProfesseur()?->getId(),
                    'matiere' => $cours->getMatiere() ? [
                        'id' => $cours->getMatiere()->getId(),
                        'libelle' => $cours->getMatiere()->getLibelle(),
                    ] : null,
                ] : null,
                'badge' => $badge ? [
                    'id' => $badge->getId(),
                    'type' => $badge->getType(),
                    'label' => $badge->getLabel(),
                ] : null,
            ];
        }, $progressions);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'by_cours', methods: ['GET'])]
    public function byCours(int $id): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        if (!$user instanceof Eleve) {
            return $this->json(['error' => 'Only students have progressions'], 403);
        }

        $cours = $this->coursRepository->find($id);

        if (!$cours instanceof Cours) {
            return $this->json(['error' => 'Course not found'], 404);
        }

        $progression = $this->progressionRepository->findOneBy([
            'eleve' => $user,
            'cours' => $cours,
        ]);

        $badgeData = null;

        if ($progression instanceof Progression && $progression->getBadge()) {
            $badge = $progression->getBadge();
            $badgeData = [
                'percentage' => $progression->getPercentage(),
                'id' => $badge->getId(),
                'type' => $badge->getType(),
                'label' => $badge->getLabel(),
            ];
        }

        $data = [
            'cours' => [
                'id' => $cours->getId(),
                'professeur' => $cours->getProfesseur()?->getId(),
                'matiere' => $cours->getMatiere() ? [
                    'id' => $cours->getMatiere()->getId(),
                    'libelle' => $cours->getMatiere()->getLibelle(),
                ] : null,
            ],
            'badge' => $badgeData,
        ];

        return $this->json($data);
    }
}