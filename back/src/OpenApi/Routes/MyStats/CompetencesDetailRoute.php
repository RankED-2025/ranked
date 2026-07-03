<?php

namespace App\OpenApi\Routes\MyStats;

class CompetencesDetailRoute extends AbstractMyStatsDetailRoute
{
    protected function getPath(): string
    {
        return '/api/my-stats/competences-detail';
    }

    protected function getOperationId(): string
    {
        return 'getMyCompetencesDetail';
    }

    protected function getSummary(): string
    {
        return 'Get the authenticated student\'s competences with acquisition status';
    }

    protected function getSuccessDescription(): string
    {
        return 'List of competences from enrolled courses, with acquired flag';
    }

    protected function getItemProperties(): array
    {
        return [
            'id'          => ['type' => 'integer'],
            'nom'         => ['type' => 'string'],
            'niveau'      => ['type' => 'string'],
            'courseId'    => ['type' => 'integer'],
            'courseTitle' => ['type' => 'string'],
            'matiere'     => ['type' => 'string'],
            'acquired'    => ['type' => 'boolean'],
        ];
    }
}
