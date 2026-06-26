<?php

namespace App\OpenApi\Routes\MyStats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class ProgressionsRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/my-stats/progressions', new PathItem(
            get: new Operation(
                operationId: 'getMyProgressions',
                tags: ['My Statistics'],
                summary: 'Get the authenticated student\'s course progressions',
                responses: [
                    '200' => [
                        'description' => 'Course progressions ordered by completion descending',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title'      => ['type' => 'string'],
                                            'percentage' => ['type' => 'integer'],
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
