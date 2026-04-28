<?php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\Progression;

class CourseStatsService
{
    public function calculateAverageProgression(Cours $cours): float|int
    {
        $progression = $cours->getProgressions();

        $total = $progression->reduce(function (float $accumulator, Progression $progression) {
            $accumulator += $progression->getPercentage();

            return $accumulator;
        }, 0.00);

        return $total / $progression->count();
    }
}
