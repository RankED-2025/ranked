<?php

namespace App\OpenApi\Routes\Courses;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class QcmSubmitRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/qcm/{id}/submit', new PathItem(
            post: new Operation(
                operationId: 'submitQuiz',
                tags: ['Quiz'],
                summary: 'Submit answers for a quiz. Grades and records the single attempt.',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'path',
                        description: 'Activity id',
                        required: true,
                        schema: ['type' => 'integer']
                    ),
                ],
                requestBody: new RequestBody(
                    description: 'Map of question id to selected answer id',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'answers' => [
                                        'type' => 'object',
                                        'description' => 'Key: question id, value: selected answer id',
                                        'additionalProperties' => ['type' => 'integer'],
                                        'example' => ['1' => 11, '2' => 21],
                                    ],
                                ],
                                'required' => ['answers'],
                            ],
                        ],
                    ])
                ),
                responses: [
                    '200' => [
                        'description' => 'Quiz graded successfully',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'score'     => ['type' => 'integer', 'example' => 2],
                                        'total'     => ['type' => 'integer', 'example' => 2],
                                        'earnedPts' => ['type' => 'integer', 'example' => 20],
                                        'gainPts'   => ['type' => 'integer', 'example' => 20],
                                    ],
                                ],
                            ],
                        ]),
                    ],
                    '400' => ['description' => 'Missing or invalid answers'],
                    '403' => ['description' => 'Student account required or not enrolled in the course'],
                    '404' => ['description' => 'Activity not found or not a quiz'],
                    '409' => ['description' => 'Quiz already submitted'],
                ]
            )
        ));
    }
}
