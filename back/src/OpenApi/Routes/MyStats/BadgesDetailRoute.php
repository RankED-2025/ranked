<?php

namespace App\OpenApi\Routes\MyStats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class BadgesDetailRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/my-stats/badges-detail', new PathItem(
            get: new Operation(
                operationId: 'getMyBadgesDetail',
                tags: ['My Statistics'],
                summary: 'Get the authenticated student\'s badge details per course',
                responses: [
                    '200' => [
                        'description' => 'Badge details for each enrolled course',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'courseId'   => ['type' => 'integer'],
                                            'courseTitle' => ['type' => 'string'],
                                            'badgeType'  => ['type' => 'string'],
                                            'badgeLabel' => ['type' => 'string'],
                                            'percentage' => ['type' => 'integer'],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ],
                    '403' => ['description' => 'Student account required'],
                ]
            )
        ));
    }
}
