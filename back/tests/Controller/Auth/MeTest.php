<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class MeTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    public function testMeWithoutAuthentication(): void
    {
        $this->get('/api/me');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testMeWithEleveAuthentication(): void
    {
        $user = EleveFactory::createOne([
            'email' => 'me.eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('me.eleve@example.com', 'password123');

        $this->get('/api/me', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertSame($user->getEmail(), $responseData['email']);
        $this->assertSame('eleve', $responseData['type']);
        $this->assertArrayHasKey('classe', $responseData);
    }

    public function testMeWithProfesseurAuthentication(): void
    {
        $user = ProfesseurFactory::createOne([
            'email' => 'me.prof@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('me.prof@example.com', 'password123');

        $this->get('/api/me', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertSame($user->getEmail(), $responseData['email']);
        $this->assertSame('professeur', $responseData['type']);
    }
}
