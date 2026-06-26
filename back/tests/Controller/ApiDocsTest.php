<?php

namespace App\Tests\Controller;

use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ApiDocsTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests;

    public function testApiDocsAreAccessible(): void
    {
        $this->get('/api/docs', ['Accept' => 'application/ld+json']);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testApiDocsWithOpenApiFormat(): void
    {
        $this->get('/api/docs', ['Accept' => 'application/vnd.openapi+json']);

        $this->assertLessThan(400, $this->getResponseCode());
    }
}
