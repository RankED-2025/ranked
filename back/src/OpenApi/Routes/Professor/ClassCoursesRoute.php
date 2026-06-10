<?php

namespace App\OpenApi\Routes\Professor;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class ClassCoursesRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/professor/classes/{id}/courses', new PathItem(
            get: new Operation(
                operationId: 'getProfessorClassCourses',
                tags: ['Professor'],
                summary: 'Get all courses assigned to a class',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'ID of the class',
                        required: true,
                        schema: ['type' => 'integer', 'minimum' => 1]
                    ),
                ],
                responses: [
                    '200' => [
                        'description' => 'List of courses assigned to the class',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id'          => ['type' => 'integer', 'example' => 1],
                                            'title'       => ['type' => 'string', 'example' => 'Introduction à l\'algèbre'],
                                            'description' => ['type' => 'string', 'nullable' => true],
                                            'matiere'     => [
                                                'type' => 'object',
                                                'nullable' => true,
                                                'properties' => [
                                                    'id'      => ['type' => 'integer'],
                                                    'libelle' => ['type' => 'string'],
                                                ],
                                            ],
                                            'difficulte'  => [
                                                'type' => 'object',
                                                'nullable' => true,
                                                'properties' => [
                                                    'id'    => ['type' => 'integer'],
                                                    'label' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ],
                    '403' => ['description' => 'Access denied'],
                    '404' => ['description' => 'Class not found'],
                ]
            )
        ));
    }
}
