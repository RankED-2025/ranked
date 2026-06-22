<?php

namespace App\Service;

use App\Entity\Activite;
use App\Entity\Eleve;
use App\Entity\Progression;
use App\Repository\ActiviteProgressionRepository;
use App\Repository\ProgressionRepository;

class ProgressionService
{
    public function __construct(
        private readonly ActiviteProgressionRepository $activiteProgressionRepository,
        private readonly ProgressionRepository $progressionRepository,
    ) {}

    public function updateCourseProgression(Eleve $eleve, Activite $activite): void
    {
        $cours = $activite->getCours();

        if (!$cours) {
            return;
        }

        $progression = $this->progressionRepository->findOneBy([
            'eleve' => $eleve,
            'cours' => $cours,
        ]);

        if (!$progression instanceof Progression) {
            return;
        }

        $totalActivites = count($cours->getActivites());

        if ($totalActivites === 0) {
            return;
        }

        $completedActivites = count($this->activiteProgressionRepository->findCompletedActiviteIds($eleve, $cours));

        $progression->setPercentage((int) round(($completedActivites / $totalActivites) * 100));
        $this->progressionRepository->save($progression, true);
    }
}
