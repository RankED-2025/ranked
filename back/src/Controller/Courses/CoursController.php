<?php

namespace App\Controller\Courses;

use App\Entity\Cours;
use App\Entity\Activite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\CoursRepository;
use App\Service\CourseMapperService;

#[Route('/api/cours', name: 'api_cours_')]
class CoursController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly CoursRepository $coursRepository,
        private readonly CourseMapperService $courseMapperService
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
            ];
        }, $coursList);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(Cours $cours): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = [
            'id' => $cours->getId(),
            'professeur' => [
                'id' => $cours->getProfesseur()?->getId(),
                'name' => $cours->getProfesseur()?->getName(),
                'firstName' => $cours->getProfesseur()?->getFirstName(),
            ],
            'matiere' => [
                'id' => $cours->getMatiere()?->getId(),
                'libelle' => $cours->getMatiere()?->getLibelle(),
            ],
            'activites' => $this->courseMapperService->mapToDefaultContentFormat($cours),
        ];

        return $this->json($data);
    }
}
