<?php

namespace App\OpenApi\Routes\MyStats;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

/**
 * Shared shape for the "my-stats" detail endpoints: each one returns an array
 * of objects for the authenticated student, and only differs in its path,
 * operation id, summary/description and item schema.
 */
abstract class AbstractMyStatsDetailRoute implements OpenApiRouteInterface
{
    /**
     * @param array<string, array<string, string>> $itemProperties
     */
    public function __construct(
        private readonly string $path,
        private readonly string $operationId,
        private readonly string $summary,
        private readonly string $successDescription,
        private readonly array $itemProperties,
    ) {
    }

    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath($this->path, new PathItem(
            get: new Operation(
                operationId: $this->operationId,
                tags: ['My Statistics'],
                summary: $this->summary,
                responses: [
                    '200' => [
                        'description' => $this->successDescription,
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => $this->itemProperties,
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
