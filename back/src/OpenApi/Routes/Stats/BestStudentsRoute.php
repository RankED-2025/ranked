<?php

namespace App\OpenApi\Routes\Stats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class BestStudentsRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/stats/best-students/{classeId}/{limit}', new PathItem(
            get: new Operation(
                operationId: 'getBestStudents',
                tags: ['Statistics'],
                summary: 'Get the top N students of a class by average progression',
                parameters: [
                    new Parameter(
                        name: 'classeId',
                        in: 'path',
                        description: 'ID of the class',
                        required: true,
                        schema: ['type' => 'integer', 'minimum' => 1]
                    ),
                    new Parameter(
                        name: 'limit',
                        in: 'path',
                        description: 'Number of students to return (defaults to 5 if omitted)',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 5, 'minimum' => 1]
                    ),
                ],
                responses: [
                    '200' => [
                        'description' => 'Ranked list of students ordered by average progression',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'rank'             => ['type' => 'integer', 'example' => 1],
                                            'name'             => ['type' => 'string', 'example' => 'Dupont'],
                                            'firstname'        => ['type' => 'string', 'example' => 'Alice'],
                                            'average'          => ['type' => 'number', 'format' => 'float', 'example' => 87.4],
                                            'completedCourses' => ['type' => 'integer', 'example' => 3],
                                            'totalCourses'     => ['type' => 'integer', 'example' => 5],
                                            'topSubject'       => ['type' => 'string', 'nullable' => true, 'example' => 'Mathématiques'],
                                        ]
                                    ]
                                ]
                            ]
                        ])
                    ],
                    '404' => ['description' => 'Class not found'],
                ]
            )
        ));
    }
}
