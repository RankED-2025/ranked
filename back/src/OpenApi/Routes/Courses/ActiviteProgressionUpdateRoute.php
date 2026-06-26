<?php

namespace App\OpenApi\Routes\Courses;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class ActiviteProgressionUpdateRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/activite-progression/{id}', new PathItem(
            put: new Operation(
                operationId: 'updateActiviteProgression',
                tags: ['Progression'],
                summary: 'Mark an activity as completed or not completed for the authenticated student',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'Activity id',
                        required: true,
                        schema: ['type' => 'integer']
                    )
                ],
                requestBody: new RequestBody(
                    description: 'Completion status of the activity',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'completed' => ['type' => 'boolean'],
                                ],
                                'required' => ['completed']
                            ]
                        ]
                    ])
                ),
                responses: [
                    '200' => [
                        'description' => 'Activity progression updated successfully',
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
                    '400' => ['description' => 'Completed is required'],
                    '403' => ['description' => 'Student account required'],
                    '404' => ['description' => 'Activity not found']
                ]
            )
        ));
    }
}
