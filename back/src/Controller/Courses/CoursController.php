<?php

namespace App\Controller\Courses;

use App\Dto\TopCoursesRequestDto;
use App\Entity\Cours;
use App\Entity\Eleve;
use App\Service\CourseStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\CoursRepository;
use App\Service\CourseMapperService;

#[Route('/api/cours', name: 'api_cours_')]
class CoursController extends AbstractController
{
    public function __construct(
        private readonly Security              $security,
        private readonly CoursRepository       $coursRepository,
        private readonly CourseMapperService   $courseMapperService,
        private readonly CourseStatsService    $courseStatsService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $coursList = $this->coursRepository->getTopCourses(10);

        $data = array_map(function (Cours $cours) {
            return [
                'id' => $cours->getId(),
                'title' => $cours->getTitre(),
                'description' => $cours->getDescription(),
                'professeur' => $cours->getProfesseur()?->getId(),
                'matiere' => $cours->getMatiere()?->getId(),
                'difficulte' => $cours->getDifficulte() ? [
                    'id' => $cours->getDifficulte()?->getId(),
                    'label' => $cours->getDifficulte()?->getLabel(),
                ] : null,
            ];
        }, $coursList);

        return $this->json($data);
    }

    #[Route('/top', name: 'top', methods: ['GET'])]
    public function getTopCourses(
        #[MapQueryString] TopCoursesRequestDto $dto
    ): JsonResponse {
        $list = $this->coursRepository->getTopCourses($dto->top);

        $data = array_map(function(Cours $cours) {
            $data = $this->courseMapperService->mapToDefaultFormat($cours);
            $data['average'] = round($this->courseStatsService->calculateAverageProgression($cours), 3);

            return $data;
        }, $list);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(int $id): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $cours = $this->coursRepository->find($id);
        if (!$cours) {
            return $this->json(['error' => 'Course not found'], 404);
        }

        $data = [
            'id' => $cours->getId(),
            'title' => $cours->getTitre(),
            'description' => $cours->getDescription(),
            'professeur' => [
                'id' => $cours->getProfesseur()?->getId(),
                'name' => $cours->getProfesseur()?->getName(),
                'firstName' => $cours->getProfesseur()?->getFirstName(),
            ],
            'matiere' => [
                'id' => $cours->getMatiere()?->getId(),
                'libelle' => $cours->getMatiere()?->getLibelle(),
            ],
            'difficulte' => $cours->getDifficulte() ? [
                'id' => $cours->getDifficulte()?->getId(),
                'label' => $cours->getDifficulte()?->getLabel(),
            ] : null,
            'activites' => $this->courseMapperService->mapToDefaultContentFormat(
                $cours,
                $user instanceof Eleve ? $user : null
            ),
        ];

        return $this->json($data);
    }
}
