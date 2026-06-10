<?php

namespace App\Controller\Professor;

use App\Entity\Classe;
use App\Entity\Eleve;
use App\Entity\Professeur;
use App\Entity\Progression;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClasseRepository;
use App\Repository\ProgressionRepository;
use App\Service\CourseMapperService;

#[Route('/api/professor/classes', name: 'api_professor_classes_')]
class ProfessorClassController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly ClasseRepository $classeRepository,
        private readonly ProgressionRepository $progressionRepository,
        private readonly CourseMapperService $courseMapper,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof Professeur) {
            return $this->json(['error' => 'Only professors can access this resource'], 403);
        }

        $classes = $this->classeRepository->findBy(['professeur' => $user]);

        $data = array_map(function (Classe $classe) {
            return [
                'id' => $classe->getId(),
                'nom' => $classe->getNom(),
            ];
        }, $classes);

        return $this->json($data);
    }

    #[Route('/{classe}/courses', name: 'get_class_courses', methods: ['GET'])]
    public function getClassCourses(Classe $classe): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof Professeur) {
            return $this->json(['error' => 'Only professors can access this resource'], 403);
        }

        if ($classe->getProfesseur()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'You are not the professor of this class'], 403);
        }

        $seenIds = [];
        $courses = [];

        foreach ($classe->getEleves() as $eleve) {
            $eleveProgression = $this->progressionRepository->findBy(['eleve' => $eleve]);

            foreach ($eleveProgression as $progression) {
                $cours = $progression->getCours();

                // unique cours entity only
                if ($cours === null || in_array($cours->getId(), $seenIds, true)) {
                    continue;
                }

                $seenIds[] = $cours->getId();
                $courses[] = $this->courseMapper->mapToProfessorCourseFormat($cours);
            }
        }

        return $this->json($courses);
    }

    #[Route('/{id}', name: 'get_class_progressions', methods: ['GET'])]
    public function studentsProgress(int $id): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof Professeur) {
            return $this->json(['error' => 'Only professors can access this resource'], 403);
        }

        $classe = $this->classeRepository->find($id);

        if (!$classe instanceof Classe) {
            return $this->json(['error' => 'Class not found'], 404);
        }

        if ($classe->getProfesseur()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'You are not the professor of this class'], 403);
        }

        $elevesData = [];

        /** @var Eleve $eleve */
        foreach ($classe->getEleves() as $eleve) {
            $progressions = $this->progressionRepository->findBy(['eleve' => $eleve]);

            $progressionsData = array_map(function (Progression $progression) {
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
                    'percentage' => $progression->getPercentage(),
                    'badge' => $badge ? [
                        'id' => $badge->getId(),
                        'type' => $badge->getType(),
                        'label' => $badge->getLabel(),
                    ] : null,
                ];
            }, $progressions);

            $elevesData[] = [
                'id' => $eleve->getId(),
                'name' => $eleve->getName(),
                'firstname' => $eleve->getFirstname(),
                'progressions' => $progressionsData,
            ];
        }

        $data = [
            'id' => $classe->getId(),
            'nom' => $classe->getNom(),
            'students' => $elevesData,
        ];

        return $this->json($data);
    }
}

