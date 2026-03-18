<?php

namespace App\Controller\Courses;

use App\Entity\Cours;
use App\Entity\Activite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\CoursRepository;

#[Route('/api/cours', name: 'api_cours_')]
class CoursController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly CoursRepository $coursRepository,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $coursList = $this->coursRepository->getTopCourses(10);

        $data = array_map(function (Cours $cours) {
            return [
                'id' => $cours->getId(),
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

        $activites = $cours->getActivites()->toArray();

        usort($activites, function (Activite $a, Activite $b) {
            return ($a->getOrdre() ?? 0) <=> ($b->getOrdre() ?? 0);
        });

        $activitesData = array_map(function (Activite $activite) {
            $contenu = $activite->getContenu();
            $qcm = $activite->getQcm();

            return [
                'id' => $activite->getId(),
                'type' => $activite->getType(),
                'ordre' => $activite->getOrdre(),
                'contenu' => $contenu ? [
                    'id' => $contenu->getId(),
                    'type' => $contenu->getType(),
                    'url' => $contenu->getUrl(),
                ] : null,
                'qcm' => $qcm ? [
                    'id' => $qcm->getId(),
                    'gainPts' => $qcm->getGainPts(),
                ] : null,
            ];
        }, $activites);

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
            'activites' => $activitesData,
        ];

        return $this->json($data);
    }
}