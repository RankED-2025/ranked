<?php

namespace App\OpenApi;

interface OpenApiRouteInterface
{
    public function addPath(\ApiPlatform\OpenApi\OpenApi $openApi): void;
}
