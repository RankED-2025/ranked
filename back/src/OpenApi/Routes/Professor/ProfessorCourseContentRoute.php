<?php

namespace App\OpenApi\Routes\Professor;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class ProfessorCourseContentRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/professor/courses/{id}', new PathItem(
            get: new Operation(
                operationId: 'getProfessorCourseContent',
                tags: ['Professor'],
                summary: 'Get full course content for a professor, including questions and correct answers.',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'Course id',
                        required: true,
                        schema: ['type' => 'integer']
                    ),
                ],
                responses: [
                    '200' => [
                        'description' => 'Full course content with activities, QCM questions, and correct answers',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id'          => ['type' => 'integer', 'example' => 1],
                                        'title'       => ['type' => 'string', 'example' => 'Introduction à l\'algèbre'],
                                        'description' => ['type' => 'string', 'nullable' => true],
                                        'matiere' => [
                                            'type' => 'object',
                                            'nullable' => true,
                                            'properties' => [
                                                'id'      => ['type' => 'integer'],
                                                'libelle' => ['type' => 'string'],
                                            ],
                                        ],
                                        'difficulte' => [
                                            'type' => 'object',
                                            'nullable' => true,
                                            'properties' => [
                                                'id'    => ['type' => 'integer'],
                                                'label' => ['type' => 'string'],
                                            ],
                                        ],
                                        'activites' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id'    => ['type' => 'integer'],
                                                    'type'  => ['type' => 'string', 'enum' => ['contenu', 'qcm']],
                                                    'ordre' => ['type' => 'integer'],
                                                    'contenu' => [
                                                        'type' => 'object',
                                                        'nullable' => true,
                                                        'properties' => [
                                                            'id'   => ['type' => 'integer'],
                                                            'type' => ['type' => 'string', 'enum' => ['article', 'video', 'pdf', 'image']],
                                                            'url'  => ['type' => 'string', 'nullable' => true],
                                                        ],
                                                    ],
                                                    'qcm' => [
                                                        'type' => 'object',
                                                        'nullable' => true,
                                                        'properties' => [
                                                            'id'       => ['type' => 'integer'],
                                                            'gainPts'  => ['type' => 'integer'],
                                                            'questions' => [
                                                                'type' => 'array',
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
                                                                                    'id'        => ['type' => 'integer'],
                                                                                    'texte'     => ['type' => 'string'],
                                                                                    'isCorrect' => ['type' => 'boolean'],
                                                                                ],
                                                                            ],
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ],
                    '403' => ['description' => 'Professor account required or not the course owner'],
                    '404' => ['description' => 'Course not found'],
                ]
            )
        ));
    }
}
