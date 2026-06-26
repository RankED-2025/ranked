<?php

namespace App\OpenApi\Routes\Stats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class ActiveStudentsPerClassRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/stats/active-students-per-class', new PathItem(
            get: new Operation(
                operationId: 'getActiveStudentsPerClass',
                tags: ['Statistics'],
                summary: 'Get number of active students (with at least one progression) per class',
                responses: [
                    '200' => [
                        'description' => 'Student count per class',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'classe' => ['type' => 'string'],
                                            'count'  => ['type' => 'integer'],
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
