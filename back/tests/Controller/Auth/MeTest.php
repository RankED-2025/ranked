<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class MeTest extends WebTestCase
{
    use ResetDatabase;

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

    private function authenticateAndGetToken($client, string $email, string $password): string
    {
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        return $responseData['token'];
    }
}
