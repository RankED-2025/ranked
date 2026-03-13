<?php

namespace App\OpenApi\Routes\Auth;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class MeRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $paths = $openApi->getPaths();

        $operation = new Operation(
            operationId: 'me',
            tags: ['Auth'],
            responses: [
                '200' => [
                    'description' => 'User information',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/User',
                            ],
                        ],
                    ],
                ],
                '401' => [
                    'description' => 'Unauthorized',
                ],
            ],
            summary: 'Get current user information',
            description: 'Returns the information of the currently authenticated user.',
        );

        $pathItem = new PathItem(
            ref: 'Me',
            get: $operation
        );

        $paths->addPath('/api/me', $pathItem);
    }
}