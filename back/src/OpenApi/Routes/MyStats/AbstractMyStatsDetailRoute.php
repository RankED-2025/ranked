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
    abstract protected function getPath(): string;

    abstract protected function getOperationId(): string;

    abstract protected function getSummary(): string;

    abstract protected function getSuccessDescription(): string;

    /**
     * @return array<string, array<string, string>>
     */
    abstract protected function getItemProperties(): array;

    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath($this->getPath(), new PathItem(
            get: new Operation(
                operationId: $this->getOperationId(),
                tags: ['My Statistics'],
                summary: $this->getSummary(),
                responses: [
                    '200' => [
                        'description' => $this->getSuccessDescription(),
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => $this->getItemProperties(),
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
