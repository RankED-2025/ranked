<?php

namespace App\OpenApi\Routes\Courses;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class TopCoursesRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/cours/top', new PathItem(
            get: new Operation(
                operationId: 'getTopCourses',
                tags: ['Courses'],
                summary: 'Get the most completed courses',
                parameters: [
                    new Parameter(
                        name: 'top',
                        in: 'query',
                        description: 'Number of courses to return',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 5, 'minimum' => 1]
                    )
                ],
                responses: [
                    '200' => [
                        'description' => 'List of top courses ordered by completion count',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'cours' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'titre' => ['type' => 'string'],
                                                    'description' => ['type' => 'string'],
                                                    'professeur' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'id' => ['type' => 'integer'],
                                                            'nom' => ['type' => 'string'],
                                                            'prenom' => ['type' => 'string'],
                                                        ]
                                                    ],
                                                    'matiere' => [
                                                        'type' => 'object',
                                                        'nullable' => true,
                                                        'properties' => [
                                                            'id' => ['type' => 'integer'],
                                                            'libelle' => ['type' => 'string'],
                                                        ]
                                                    ],
                                                    'difficulte' => [
                                                        'type' => 'object',
                                                        'nullable' => true,
                                                        'properties' => [
                                                            'id' => ['type' => 'integer'],
                                                            'label' => ['type' => 'string'],
                                                        ]
                                                    ],
                                                ]
                                            ],
                                            'pourcentage' => ['type' => 'number', 'nullable' => true],
                                            'badge' => [
                                                'type' => 'object',
                                                'nullable' => true,
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'type' => ['type' => 'string'],
                                                    'label' => ['type' => 'string'],
                                                ]
                                            ],
                                            'average' => ['type' => 'number', 'format' => 'float'],
                                        ]
                                    ]
                                ]
                            ]
                        ])
                    ],
                    '422' => ['description' => 'Validation error (top must be a positive integer)']
                ]
            )
        ));
    }
}
