<?php

namespace App\Tests\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class PasswordResetTest extends WebTestCase
{
    use ResetDatabase;

    public function testRequestWithInvalidPayload(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/password-reset/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'not-an-email'])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRequestWithUnknownEmailStillSucceeds(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/password-reset/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'unknown@example.com'])
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
    }

    public function testConfirmWithInvalidPayload(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/password-reset/confirm',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['token' => '', 'password' => 'short'])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testConfirmWithInvalidToken(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/password-reset/confirm',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'token' => 'invalid-token',
                'password' => 'newPassword123',
            ])
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
