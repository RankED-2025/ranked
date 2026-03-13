<?php

namespace App\OpenApi\Routes\Auth;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class LogoutRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/logout', new PathItem(
            post: new Operation(
                operationId: 'logout',
                tags: ['Auth'],
                summary: 'Logout user',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    description: 'Refresh token to invalidate',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'refresh_token' => ['type' => 'string']
                                ],
                                'required' => ['refresh_token']
                            ]
                        ]
                    ])
                ),
                responses: [
                    '204' => ['description' => 'User logged out'],
                    '400' => ['description' => 'Bad request'],
                    '422' => ['description' => 'Invalid refresh token']
                ]
            )
        ));
    }
}