<?php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\Progression;
use App\Entity\Badge;

class CourseMapperService
{
    public function mapToDefaultFormat(Cours $cours, Progression $progression, Badge $badge): array
    {
        return [
            'cours' => $cours ? [
                'id' => $cours->getId(),
                'professeur' => [
                    'id' => $cours->getProfesseur()?->getId(),
                    'name' => $cours->getProfesseur()?->getName(),
                    'firstName' => $cours->getProfesseur()?->getFirstName(),
                ],
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
    }
}