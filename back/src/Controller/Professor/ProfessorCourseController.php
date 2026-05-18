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
                'title' => $course->getTitre(),
                'description' => $course->getDescription(),
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

    #[Route('/edit/{id}', name: 'edit_course_content', methods: ['POST'])]
    public function editCourseContent(Request $request, int $id): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof Professeur) {
            return $this->json(['error' => 'Only professors can edit courses'], 403);
        }

        $cours = $this->coursRepository->find($id);

        if (!$cours instanceof Cours) {
            return $this->json(['error' => 'Course not found'], 404);
        }

        if ($cours->getProfesseur()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'You are not the owner of this course'], 403);
        }

        $data = json_decode($request->getContent(), true) ?: [];

        if (array_key_exists('title', $data)) {
            $cours->setTitre($data['title']);
        }

        if (array_key_exists('description', $data)) {
            $cours->setDescription($data['description']);
        }

        if (array_key_exists('matiere_id', $data) && $data['matiere_id'] !== null) {
            $matiere = $this->entityManager->getRepository(\App\Entity\Matiere::class)->find($data['matiere_id']);
            if (!$matiere) {
                return $this->json(['error' => 'Matiere not found'], 404);
            }
            $cours->setMatiere($matiere);
        }

        if (array_key_exists('difficulte_id', $data)) {
            if ($data['difficulte_id'] === null) {
                $cours->setDifficulte(null);
            } else {
                $difficulte = $this->entityManager->getRepository(Difficulte::class)->find($data['difficulte_id']);
                if (!$difficulte) {
                    return $this->json(['error' => 'Difficulte not found'], 404);
                }
                $cours->setDifficulte($difficulte);
            }
        }

        if (array_key_exists('activites', $data) && is_array($data['activites'])) {
            $existing = [];
            foreach ($cours->getActivites() as $a) {
                if ($a->getId()) {
                    $existing[$a->getId()] = $a;
                }
            }

            $receivedIds = [];

            foreach ($data['activites'] as $actData) {
                $actId = $actData['id'] ?? null;

                if ($actId && isset($existing[$actId])) {
                    $activite = $existing[$actId];
                } else {
                    $activite = new \App\Entity\Activite();
                    $activite->setCours($cours);
                    $this->entityManager->persist($activite);
                    $cours->addActivite($activite);
                }

                if (array_key_exists('type', $actData)) {
                    $activite->setType($actData['type']);
                }

                if (array_key_exists('ordre', $actData)) {
                    $activite->setOrdre((int)$actData['ordre']);
                }

                if (array_key_exists('contenu', $actData) && is_array($actData['contenu'])) {
                    $contenuData = $actData['contenu'];
                    $contenu = $activite->getContenu();
                    $hasContentData = (
                        (array_key_exists('type', $contenuData) && $contenuData['type'] !== null && $contenuData['type'] !== '') ||
                        (array_key_exists('url', $contenuData) && $contenuData['url'] !== null && $contenuData['url'] !== '')
                    );

                    if (!$contenu && $hasContentData) {
                        $contenu = new \App\Entity\Contenu();
                        $this->entityManager->persist($contenu);
                        $activite->setContenu($contenu);
                    }
                    if ($contenu && array_key_exists('type', $contenuData) && $contenuData['type'] !== null && $contenuData['type'] !== '') {
                        $contenu->setType($contenuData['type']);
                    }
                    if ($contenu && array_key_exists('url', $contenuData) && $contenuData['url'] !== null && $contenuData['url'] !== '') {
                        $contenu->setUrl($contenuData['url']);
                    }
                }

                if (array_key_exists('qcm', $actData) && is_array($actData['qcm'])) {
                    $qcmData = $actData['qcm'];
                    $qcm = $activite->getQcm();
                    if (!$qcm) {
                        $qcm = new \App\Entity\Qcm();
                        $this->entityManager->persist($qcm);
                        $activite->setQcm($qcm);
                    }
                    if (array_key_exists('gainPts', $qcmData)) {
                        $qcm->setGainPts((int)$qcmData['gainPts']);
                    }
                }

                if ($activite->getId()) {
                    $receivedIds[] = $activite->getId();
                }
            }

            foreach ($cours->getActivites() as $a) {
                if ($a->getId() && !in_array($a->getId(), $receivedIds)) {
                    $cours->removeActivite($a);
                    $this->entityManager->remove($a);
                }
            }
        }

        if (array_key_exists('competences', $data) && is_array($data['competences'])) {
            $existingC = [];
            foreach ($cours->getCompetences() as $c) {
                if ($c->getId()) {
                    $existingC[$c->getId()] = $c;
                }
            }

            $receivedCompIds = [];

            foreach ($data['competences'] as $compData) {
                $compId = $compData['id'] ?? null;

                if ($compId && isset($existingC[$compId])) {
                    $competence = $existingC[$compId];
                } else {
                    $competence = new \App\Entity\Competence();
                    $competence->setCours($cours);
                    $this->entityManager->persist($competence);
                    $cours->addCompetence($competence);
                }

                if (array_key_exists('nom', $compData)) {
                    $competence->setNom($compData['nom']);
                }

                if (array_key_exists('niveau', $compData)) {
                    $competence->setNiveau($compData['niveau']);
                }

                if ($competence->getId()) {
                    $receivedCompIds[] = $competence->getId();
                }
            }

            foreach ($cours->getCompetences() as $c) {
                if ($c->getId() && !in_array($c->getId(), $receivedCompIds)) {
                    $cours->removeCompetence($c);
                    $this->entityManager->remove($c);
                }
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Course updated successfully',
            'id' => $cours->getId(),
        ]);
    }

    #[Route('/{id}', name: 'delete_course', methods: ['DELETE'])]
    public function deleteCourse(int $id): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof Professeur) {
            return $this->json(['error' => 'Only professors can delete courses'], 403);
        }

        $cours = $this->coursRepository->find($id);

        if (!$cours instanceof Cours) {
            return $this->json(['error' => 'Course not found'], 404);
        }

        if ($cours->getProfesseur()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'You are not the owner of this course'], 403);
        }

        foreach ($cours->getCompetences() as $competence) {
            foreach ($competence->getEleveCompetences() as $eleveCompetence) {
                $this->entityManager->remove($eleveCompetence);
            }
        }

        foreach ($cours->getCompetences() as $competence) {
            $this->entityManager->remove($competence);
        }

        foreach ($cours->getActivites() as $activite) {
            $this->entityManager->remove($activite);
        }

        foreach ($cours->getProgressions() as $progression) {
            $this->entityManager->remove($progression);
        }

        $this->entityManager->remove($cours);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Course deleted successfully',
        ]);
    }
}
