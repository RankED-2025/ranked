<?php

namespace App\OpenApi\Routes\Courses;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class ProgressionUpdateRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/progression/{id}', new PathItem(
            put: new Operation(
                operationId: 'updateProgression',
                tags: ['Progression'],
                summary: 'Update the authenticated student\'s progression for a course',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'Course id',
                        required: true,
                        schema: ['type' => 'integer']
                    )
                ],
                requestBody: new RequestBody(
                    description: 'New progression percentage',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'percentage' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                                ],
                                'required' => ['percentage']
                            ]
                        ]
                    ])
                ),
                responses: [
                    '200' => [
                        'description' => 'Progression updated successfully',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                    ]
                                ]
                            ]
                        ])
                    ],
                    '400' => ['description' => 'Percentage is required'],
                    '403' => ['description' => 'Student account required'],
                    '404' => ['description' => 'Course not found or no progression for this course']
                ]
            )
        ));
    }
}
