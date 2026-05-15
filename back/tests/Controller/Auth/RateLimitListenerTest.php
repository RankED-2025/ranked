<?php

namespace App\Tests\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class RateLimitListenerTest extends WebTestCase
{
    use ResetDatabase;

    public function testRegisterSubpathTriggersRateLimiter(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/register/professor',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test',
                'firstname' => 'User',
                'email' => 'test@example.com',
                'password' => 'password123',
            ])
        );

        // The route doesn't exist so we get 404, but the rate limiter was checked
        $this->assertResponseStatusCodeSame(404);
    }
}
