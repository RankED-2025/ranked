<?php

namespace App\OpenApi\Routes\Courses;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class QcmShowRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/qcm/{id}', new PathItem(
            get: new Operation(
                operationId: 'getQuiz',
                tags: ['Quiz'],
                summary: 'Get a quiz for the authenticated student. Returns the locked result if already submitted.',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'Activity id',
                        required: true,
                        schema: ['type' => 'integer']
                    ),
                ],
                responses: [
                    '200' => [
                        'description' => 'Quiz data. When locked, questions are omitted and the result is returned instead.',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id'       => ['type' => 'integer', 'example' => 7],
                                        'gainPts'  => ['type' => 'integer', 'example' => 20],
                                        'locked'   => ['type' => 'boolean'],
                                        'questions' => [
                                            'type' => 'array',
                                            'nullable' => true,
                                            'description' => 'Present only when locked is false',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id'       => ['type' => 'integer'],
                                                    'enonce'   => ['type' => 'string'],
                                                    'reponses' => [
                                                        'type' => 'array',
                                                        'items' => [
                                                            'type' => 'object',
                                                            'properties' => [
                                                                'id'    => ['type' => 'integer'],
                                                                'texte' => ['type' => 'string'],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'result' => [
                                            'type' => 'object',
                                            'nullable' => true,
                                            'description' => 'Present only when locked is true',
                                            'properties' => [
                                                'score'     => ['type' => 'integer'],
                                                'total'     => ['type' => 'integer'],
                                                'earnedPts' => ['type' => 'integer'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ],
                    '403' => ['description' => 'Student account required or not enrolled in the course'],
                    '404' => ['description' => 'Activity not found or not a quiz'],
                ]
            )
        ));
    }
}
