<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Tests\Traits\AuthenticatesUsers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class PasswordResetTest extends WebTestCase
{
    use ResetDatabase;
    use AuthenticatesUsers;

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

    public function testRequestWithKnownEmailSendsReset(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'known.user@example.com',
            'password' => 'password123',
        ]);

        $client->request(
            'POST',
            '/api/password-reset/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'known.user@example.com'])
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
    }

    public function testConfirmWithValidToken(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'reset.user@example.com',
            'password' => 'oldPassword123',
        ]);

        $client->request(
            'POST',
            '/api/password-reset/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'reset.user@example.com'])
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $token = $em->getRepository(\App\Entity\PasswordResetToken::class)->findOneBy([], ['id' => 'DESC']);

        $this->assertNotNull($token);

        $client->request(
            'POST',
            '/api/password-reset/confirm',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'token' => $token->getToken(),
                'password' => 'newPassword123',
            ])
        );

        $this->assertResponseStatusCodeSame(200);
    }
}
