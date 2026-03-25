<?php

namespace App\OpenApi\Routes\Auth;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class LoginRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/login', new PathItem(
            post: new Operation(
                operationId: 'login',
                tags: ['Auth'],
                summary: 'Login user and get JWT token',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    description: 'User credentials',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => ['type' => 'string', 'format' => 'email'],
                                    'password' => ['type' => 'string', 'format' => 'password']
                                ],
                                'required' => ['email', 'password']
                            ]
                        ]
                    ])
                ),
                responses: [
                    '200' => [
                        'description' => 'Successful login',
                        'content' => new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ])
                    ],
                    '401' => ['description' => 'Invalid credentials']
                ]
            )
        ));
    }   
}