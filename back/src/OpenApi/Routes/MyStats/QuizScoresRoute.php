<?php

namespace App\OpenApi\Routes\MyStats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class QuizScoresRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/my-stats/quiz-scores', new PathItem(
            get: new Operation(
                operationId: 'getMyQuizScores',
                tags: ['My Statistics'],
                summary: 'Get quiz scores for courses the authenticated student is enrolled in',
                responses: [
                    '200' => [
                        'description' => 'Quiz scores ordered by course then activity order',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'label'  => ['type' => 'string', 'example' => 'Algorithmique – Q1'],
                                            'points' => ['type' => 'integer'],
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
