<?php

namespace App\OpenApi\Routes\MyStats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class BadgesRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/my-stats/badges', new PathItem(
            get: new Operation(
                operationId: 'getMyBadges',
                tags: ['My Statistics'],
                summary: 'Get the authenticated student\'s earned badge distribution by type',
                responses: [
                    '200' => [
                        'description' => 'Badge count per type, ordered by count descending',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'type'  => ['type' => 'string'],
                                            'count' => ['type' => 'integer'],
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
