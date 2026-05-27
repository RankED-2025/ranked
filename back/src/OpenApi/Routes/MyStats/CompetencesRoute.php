<?php

namespace App\OpenApi\Routes\MyStats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class CompetencesRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/my-stats/competences', new PathItem(
            get: new Operation(
                operationId: 'getMyCompetences',
                tags: ['My Statistics'],
                summary: 'Get the authenticated student\'s competence acquisition percentage per subject',
                responses: [
                    '200' => [
                        'description' => 'Percentage of acquired competences per subject',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'matiere'    => ['type' => 'string'],
                                            'percentage' => ['type' => 'number', 'format' => 'float'],
                                        ]
                                    ]
                                ]
                            ]
                        ])
                    ],
                    '403' => ['description' => 'Student account required']
                ]
            )
        ));
    }
}
