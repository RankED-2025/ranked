<?php

namespace App\OpenApi\Routes\MyStats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class CompetencesDetailRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/my-stats/competences-detail', new PathItem(
            get: new Operation(
                operationId: 'getMyCompetencesDetail',
                tags: ['My Statistics'],
                summary: 'Get the authenticated student\'s competences with acquisition status',
                responses: [
                    '200' => [
                        'description' => 'List of competences from enrolled courses, with acquired flag',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id'          => ['type' => 'integer'],
                                            'nom'         => ['type' => 'string'],
                                            'niveau'      => ['type' => 'string'],
                                            'courseId'    => ['type' => 'integer'],
                                            'courseTitle' => ['type' => 'string'],
                                            'matiere'     => ['type' => 'string'],
                                            'acquired'    => ['type' => 'boolean'],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ],
                    '403' => ['description' => 'Student account required'],
                ]
            )
        ));
    }
}
