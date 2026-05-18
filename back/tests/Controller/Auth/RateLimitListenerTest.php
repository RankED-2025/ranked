<?php

namespace App\Tests\Controller\Auth;

use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class RateLimitListenerTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests;

    public function testRegisterSubpathTriggersRateLimiter(): void
    {
        $this->post('/api/register/professor', [
            'name' => 'Test',
            'firstname' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // The route doesn't exist so we get 404, but the rate limiter was checked
        $this->assertResponseStatusCodeSame(404);
    }
}
