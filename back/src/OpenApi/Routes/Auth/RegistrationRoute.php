<?php

namespace App\OpenApi\Routes\Auth;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiRouteInterface;

class RegistrationRoute implements OpenApiRouteInterface
{
    public function addPath(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath('/api/register', new PathItem(
            post: new Operation(
                operationId: 'register',
                tags: ['Auth'],
                summary: 'Register a new user',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    description: 'User registration data',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => ['type' => 'string', 'format' => 'email'],
                                    'password' => ['type' => 'string', 'format' => 'password'],
                                    'firstname' => ['type' => 'string'],
                                    'name' => ['type' => 'string']
                                ],
                                'required' => ['email', 'password', 'firstname', 'name']
                            ]
                        ]
                    ])
                ),
                responses: [
                    '201' => ['description' => 'User registered successfully'],
                    '400' => ['description' => 'Invalid input data']
                ]
            )
        ));
    }
}
