<?php

namespace App\OpenApi\Routes\Stats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class CompletionBySubjectRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/stats/completion-by-subject', new PathItem(
            get: new Operation(
                operationId: 'getCompletionBySubject',
                tags: ['Statistics'],
                summary: 'Get average course completion percentage grouped by subject',
                responses: [
                    '200' => [
                        'description' => 'Average completion per subject, ordered by completion descending',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'subject' => ['type' => 'string'],
                                            'average' => ['type' => 'number', 'format' => 'float'],
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
