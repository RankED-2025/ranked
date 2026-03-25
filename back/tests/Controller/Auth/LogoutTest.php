<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class LogoutTest extends WebTestCase
{
    use ResetDatabase;

    public function testLogoutWithoutAuthentication(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/logout',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['refresh_token' => 'any'])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLogoutWithMissingRefreshToken(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'logout.user@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'logout.user@example.com', 'password123');

        $client->request(
            'POST',
            '/api/logout',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLogoutWithInvalidRefreshToken(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'logout.user2@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'logout.user2@example.com', 'password123');

        $client->request(
            'POST',
            '/api/logout',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode(['refresh_token' => 'invalid-token'])
        );

        $this->assertResponseStatusCodeSame(422);
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
