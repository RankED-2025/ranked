<?php

namespace App\OpenApi\Routes\Stats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class BadgeDistributionRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/stats/badge-distribution', new PathItem(
            get: new Operation(
                operationId: 'getBadgeDistribution',
                tags: ['Statistics'],
                summary: 'Get global distribution of earned badges by type',
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
                    ]
                ]
            )
        ));
    }
}
