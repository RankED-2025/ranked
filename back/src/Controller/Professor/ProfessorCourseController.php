<?php

namespace App\Controller\Professor;

use App\Entity\Classe;
use App\Entity\Cours;
use App\Entity\Difficulte;
use App\Entity\Professeur;
use App\Repository\ClasseRepository;
use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/professor/courses', name: 'api_professor_courses_')]
class ProfessorCourseController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly CoursRepository $coursRepository,
        private readonly ClasseRepository $classeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof Professeur) {
            return $this->json(['error' => 'Only professors can create courses'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $matiereId = $data['matiere_id'] ?? null;
        $difficulteId = $data['difficulte_id'] ?? null;
        $title = $data['title'] ?? null;
        $description = $data['description'] ?? null;

        if(!$title || !$description) {
            return $this->json(['error' => 'title and description are required'], 400);
        }

        if ($matiereId === null) {
            return $this->json(['error' => 'matiere_id is required'], 400);
        }

        $matiere = $this->entityManager->getRepository(\App\Entity\Matiere::class)->find($matiereId);

        if (!$matiere) {
            return $this->json(['error' => 'Matiere not found'], 404);
        }

        $difficulte = null;
        if ($difficulteId !== null) {
            $difficulte = $this->entityManager->getRepository(Difficulte::class)->find($difficulteId);

            if (!$difficulte) {
                return $this->json(['error' => 'Difficulte not found'], 404);
            }
        }

        if (!$difficulte) {
            $difficulte = $this->entityManager->getRepository(Difficulte::class)->findOneBy([], ['id' => 'ASC']);
        }

        $cours = new Cours();
        $cours->setTitre($title);
        $cours->setDescription($description);
        $cours->setProfesseur($user);
        $cours->setMatiere($matiere);
        $cours->setDifficulte($difficulte);

        $this->entityManager->persist($cours);
        $this->entityManager->flush();

        return $this->json([
            'id' => $cours->getId(),
            'professeur' => $cours->getProfesseur()?->getId(),
            'matiere' => $cours->getMatiere()?->getId(),
            'difficulte' => $cours->getDifficulte() ? [
                'id' => $cours->getDifficulte()?->getId(),
                'label' => $cours->getDifficulte()?->getLabel(),
            ] : null,
        ], 201);
    }

    #[Route('/assign', name: 'assign_to_class', methods: ['POST'])]
    public function assignToClass(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof Professeur) {
            return $this->json(['error' => 'Only professors can assign courses'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $classeId = $data['classe_id'] ?? null;
        $coursId = $data['cours_id'] ?? null;

        if ($classeId === null || $coursId === null) {
            return $this->json(['error' => 'classe_id and cours_id are required'], 400);
        }

        $classe = $this->classeRepository->find($classeId);
        $cours = $this->coursRepository->find($coursId);

        if (!$classe instanceof Classe) {
            return $this->json(['error' => 'Class not found'], 404);
        }

        if (!$cours instanceof Cours) {
            return $this->json(['error' => 'Course not found'], 404);
        }

        if ($classe->getProfesseur()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'You are not the professor of this class'], 403);
        }

        if ($cours->getProfesseur()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'You are not the owner of this course'], 403);
        }

        // Ici, on modélise "un cours assigné à une classe" comme :
        // pour chaque élève de la classe, s'il n'a pas encore de progression sur ce cours on la crée
        foreach ($classe->getEleves() as $eleve) {
            $existing = $this->entityManager->getRepository(\App\Entity\Progression::class)->findOneBy([
                'eleve' => $eleve,
                'cours' => $cours,
            ]);

            if ($existing) {
                continue;
            }

            $progression = new \App\Entity\Progression();
            $progression->setEleve($eleve);
            $progression->setCours($cours);

            $badge = $this->entityManager->getRepository(\App\Entity\Badge::class)->findOneBy(['type' => 'bronze']);
            if ($badge) {
                $progression->setBadge($badge);
            }

            $progression->setPercentage(0);
            $this->entityManager->persist($progression);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Course assigned to class successfully',
            'classe_id' => $classe->getId(),
            'cours_id' => $cours->getId(),
        ]);
    }

    #[Route('', name: 'get_my_courses', methods: ['GET'])]
    public function getMyCourses(): JsonResponse {
        $user = $this->security->getUser();

        if (!$user instanceof Professeur) {
            return $this->json(['error' => 'Only professors can access this resource'], 403);
        }

        $courses = $this->coursRepository->findBy(['professeur' => $user]);

        $data = array_map(function (Cours $course) {
            return [
                'id' => $course->getId(),
                'matiere' => $course->getMatiere() ? [
                    'id' => $course->getMatiere()->getId(),
                    'libelle' => $course->getMatiere()->getLibelle(),
                ] : null,
                'difficulte' => $course->getDifficulte() ? [
                    'id' => $course->getDifficulte()->getId(),
                    'label' => $course->getDifficulte()->getLabel(),
                ] : null,
            ];
        }, $courses);

        return $this->json($data);
    }
}

