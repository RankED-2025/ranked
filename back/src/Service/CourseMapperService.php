<?php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\Difficulte;
use App\Entity\Matiere;
use App\Entity\Progression;
use App\Entity\Badge;
use App\Entity\Activite;

class CourseMapperService
{
    public function mapToDefaultFormat(Cours $cours, ?Progression $progression = null, ?Badge $badge = null): array
    {
        return [
            'cours' => $cours ? [
                'id' => $cours->getId(),
                'professeur' => [
                    'id' => $cours->getProfesseur()?->getId(),
                    'nom' => $cours->getProfesseur()?->getName(),
                    'prenom' => $cours->getProfesseur()?->getFirstName(),
                ],
                'titre' => $cours->getTitre(),
                'description' => $cours->getDescription(),
                'matiere' => $cours->getMatiere() ? [
                    'id' => $cours->getMatiere()->getId(),
                    'libelle' => $cours->getMatiere()->getLibelle(),
                ] : null,
                'difficulte' => $cours->getDifficulte() ? [
                    'id' => $cours->getDifficulte()->getId(),
                    'label' => $cours->getDifficulte()->getLabel(),
                ] : null,
            ] : null,
            'pourcentage' => $progression ? $progression->getPercentage() : null,
            'badge' => $badge ? [
                'id' => $badge->getId(),
                'type' => $badge->getType(),
                'label' => $badge->getLabel(),
            ] : null,
        ];
    }

    public function mapToProfessorCourseFormat(Cours $cours): array
    {
        /** @var Matiere | null $matiere */
        $matiere = $cours->getMatiere();

        /** @var Difficulte | null $difficulte */
        $difficulte = $cours->getDifficulte();

        return [
            'id'          => $cours->getId(),
            'title'       => $cours->getTitre(),
            'description' => $cours->getDescription(),
            'matiere'     => $matiere ? [
                'id'      => $matiere->getId(),
                'libelle' => $matiere->getLibelle(),
            ] : null,
            'difficulte'  => $difficulte ? [
                'id'    => $difficulte->getId(),
                'label' => $difficulte->getLabel(),
            ] : null,
        ];
    }

    public function mapToDefaultContentFormat(Cours $cours): array
    {
        $activites = $cours->getActivites()->toArray();

        usort($activites, function (Activite $a, Activite $b) {
            return ($a->getOrdre() ?? 0) <=> ($b->getOrdre() ?? 0);
        });

        return array_map(function (Activite $activite) {
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
    }
}
