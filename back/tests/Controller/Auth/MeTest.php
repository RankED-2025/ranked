<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\AuthenticatesUsers;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class MeTest extends WebTestCase
{
    use ResetDatabase;
    use AuthenticatesUsers;

    public function testMeWithoutAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/me');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testMeWithEleveAuthentication(): void
    {
        $client = self::createClient();

        $user = EleveFactory::createOne([
            'email' => 'me.eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'me.eleve@example.com', 'password123');

        $client->request(
            'GET',
            '/api/me',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($user->getEmail(), $responseData['email']);
        $this->assertSame('eleve', $responseData['type']);
        $this->assertArrayHasKey('classe', $responseData);
    }

    public function testMeWithProfesseurAuthentication(): void
    {
        $client = self::createClient();

        $user = ProfesseurFactory::createOne([
            'email' => 'me.prof@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'me.prof@example.com', 'password123');

        $client->request(
            'GET',
            '/api/me',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($user->getEmail(), $responseData['email']);
        $this->assertSame('professeur', $responseData['type']);
    }

}
