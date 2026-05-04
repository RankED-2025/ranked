<?php

namespace App\Controller\Referentiels;

use App\Entity\Difficulte;
use App\Entity\Matiere;
use App\Repository\DifficulteRepository;
use App\Repository\MatiereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/referentiels', name: 'api_referentiels_')]
class ReferentielController extends AbstractController
{
    public function __construct(
        private readonly MatiereRepository $matiereRepository,
        private readonly DifficulteRepository $difficulteRepository
    ) {}

    #[Route('/matieres', name: 'matieres', methods: ['GET'])]
    public function matieres(): JsonResponse
    {
        $matieres = $this->matiereRepository->findBy([], ['libelle' => 'ASC']);

        $data = array_map(static function (Matiere $matiere): array {
            return [
                'id' => $matiere->getId(),
                'libelle' => $matiere->getLibelle(),
            ];
        }, $matieres);

        return $this->json($data);
    }

    #[Route('/difficultes', name: 'difficultes', methods: ['GET'])]
    public function difficultes(): JsonResponse
    {
        $difficultes = $this->difficulteRepository->findBy([], ['label' => 'ASC']);

        $data = array_map(static function (Difficulte $difficile): array {
            return [
                'id' => $difficile->getId(),
                'label' => $difficile->getLabel(),
            ];
        }, $difficultes);

        return $this->json($data);
    }
}