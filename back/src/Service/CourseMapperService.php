<?php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\Difficulte;
use App\Entity\Eleve;
use App\Entity\Matiere;
use App\Entity\Progression;
use App\Entity\Badge;
use App\Entity\Activite;
use App\Repository\ActiviteProgressionRepository;

class CourseMapperService
{
    public function __construct(
        private readonly ActiviteProgressionRepository $activiteProgressionRepository,
    ) {}

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

    public function mapToDefaultContentFormat(Cours $cours, ?Eleve $eleve = null): array
    {
        $activites = $cours->getActivites()->toArray();

        usort($activites, fn(Activite $a, Activite $b) => ($a->getOrdre() ?? 0) <=> ($b->getOrdre() ?? 0));

        $completedActiviteIds = $eleve
            ? $this->activiteProgressionRepository->findCompletedActiviteIds($eleve, $cours)
            : [];

        return array_map(function (Activite $activite) use ($completedActiviteIds) {
            $contenu = $activite->getContenu();
            $qcm = $activite->getQcm();

            return [
                'id' => $activite->getId(),
                'type' => $activite->getType(),
                'ordre' => $activite->getOrdre(),
                'completed' => in_array($activite->getId(), $completedActiviteIds, true),
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

    /**
     * Maps a course's activities for the professor editor, including the full QCM content
     * (questions and answers with their isCorrect flag). Never expose this to students.
     */
    public function mapToProfessorContentFormat(Cours $cours): array
    {
        $activites = $cours->getActivites()->toArray();

        usort($activites, fn(Activite $a, Activite $b) => ($a->getOrdre() ?? 0) <=> ($b->getOrdre() ?? 0));

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
                    'questions' => array_map(fn($question) => [
                        'id' => $question->getId(),
                        'enonce' => $question->getEnonce(),
                        'reponses' => array_map(fn($reponse) => [
                            'id' => $reponse->getId(),
                            'texte' => $reponse->getTexte(),
                            'isCorrect' => $reponse->isCorrect(),
                        ], $question->getReponses()->toArray()),
                    ], $qcm->getQuestions()->toArray()),
                ] : null,
            ];
        }, $activites);
    }
}
