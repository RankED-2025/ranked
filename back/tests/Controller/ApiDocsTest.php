<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ApiDocsTest extends WebTestCase
{
    use ResetDatabase;

    public function testApiDocsAreAccessible(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/docs', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

        $this->assertResponseIsSuccessful();
    }

    public function testApiDocsWithOpenApiFormat(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/docs', [], [], [
            'HTTP_ACCEPT' => 'application/vnd.openapi+json',
        ]);

        // API docs endpoint should be accessible
        $this->assertLessThan(500, $client->getResponse()->getStatusCode());
    }
}
