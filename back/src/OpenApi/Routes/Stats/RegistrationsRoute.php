<?php

namespace App\OpenApi\Routes\Stats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class RegistrationsRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/stats/registrations', new PathItem(
            get: new Operation(
                operationId: 'getRegistrations',
                tags: ['Statistics'],
                summary: 'Get new-student registration counts per week over the last 8 weeks, for the authenticated professor\'s own classes',
                responses: [
                    '200' => [
                        'description' => 'Registration count per ISO week (e.g. "2025-W03")',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'week'  => ['type' => 'string', 'example' => '2025-W03'],
                                            'count' => ['type' => 'integer'],
                                        ]
                                    ]
                                ]
                            ]
                        ])
                    ],
                    '403' => ['description' => 'Caller is not a professor'],
                ]
            )
        ));
    }
}
