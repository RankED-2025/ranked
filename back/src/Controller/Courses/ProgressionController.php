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
use Symfony\Component\HttpFoundation\Request;

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
                'cours' => $cours ? [
                    'id' => $cours->getId(),
                    'professeur' => $cours->getProfesseur()?->getId(),
                    'matiere' => $cours->getMatiere() ? [
                        'id' => $cours->getMatiere()->getId(),
                        'libelle' => $cours->getMatiere()->getLibelle(),
                    ] : null,
                ] : null,
                'percentage' => $progression ? $progression->getPercentage() : null,
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
            'percentage' => $progression->getPercentage(),
            'badge' => $badgeData,
        ];

        return $this->json($data);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        if (!$user instanceof Eleve) {
            return $this->json(['error' => 'Only students can update their progression'], 403);
        }

        $cours = $this->coursRepository->find($id);

        if (!$cours instanceof Cours) {
            return $this->json(['error' => 'Course not found'], 404);
        }

        $progression = $this->progressionRepository->findOneBy([
            'eleve' => $user,
            'cours' => $cours,
        ]);

        if (!$progression instanceof Progression) {
            return $this->json(['error' => 'Progression not found for this course'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $percentage = $data['percentage'] ?? null;

        if ($percentage === null) {
            return $this->json(['error' => 'Percentage is required'], 400);
        }

        $progression->setPercentage($percentage);
        $this->progressionRepository->save($progression, true);

        return $this->json(['message' => 'Progression updated successfully'], 200);
    }
}