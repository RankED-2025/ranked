<?php

namespace App\OpenApi\Routes\MyStats;

class CompetencesDetailRoute extends AbstractMyStatsDetailRoute
{
    public function __construct()
    {
        parent::__construct(
            path: '/api/my-stats/competences-detail',
            operationId: 'getMyCompetencesDetail',
            summary: 'Get the authenticated student\'s competences with acquisition status',
            successDescription: 'List of competences from enrolled courses, with acquired flag',
            itemProperties: [
                'id'          => ['type' => 'integer'],
                'nom'         => ['type' => 'string'],
                'niveau'      => ['type' => 'string'],
                'courseId'    => ['type' => 'integer'],
                'courseTitle' => ['type' => 'string'],
                'matiere'     => ['type' => 'string'],
                'acquired'    => ['type' => 'boolean'],
            ],
        );
    }
}
