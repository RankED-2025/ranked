<?php

namespace App\OpenApi\Routes\MyStats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class ClassRankRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/my-stats/class-rank', new PathItem(
            get: new Operation(
                operationId: 'getMyClassRank',
                tags: ['My Statistics'],
                summary: 'Get the authenticated student\'s rank within their class',
                responses: [
                    '200' => [
                        'description' => 'Student rank information within their class',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'myAverage'  => ['type' => 'number', 'format' => 'float'],
                                        'rank'       => ['type' => 'integer'],
                                        'total'      => ['type' => 'integer'],
                                        'percentile' => ['type' => 'number', 'format' => 'float'],
                                    ]
                                ]
                            ]
                        ])
                    ],
                    '403' => ['description' => 'Student account required'],
                    '404' => ['description' => 'No class or progression data available']
                ]
            )
        ));
    }
}
