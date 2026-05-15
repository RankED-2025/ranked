<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class RegisterTest extends WebTestCase
{
    use ResetDatabase;

    /**
     * Test successful register with valid payload
     */
    public function testRegisterSuccess(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Doe',
                'firstname' => 'John',
                'email' => 'john.doe@example.com',
                'password' => 'password123',
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertIsString($responseData['token']);
        $this->assertNotEmpty($responseData['token']);
    }

    /**
     * Test register failure with already used email
     */
    public function testRegisterFailureWithExistingEmail(): void
    {
        $client = self::createClient();
        EleveFactory::createOne([
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Dupont',
                'firstname' => 'Jean',
                'email' => 'existing@example.com',
                'password' => 'password123',
            ])
        );

        $this->assertResponseStatusCodeSame(409);
    }

    /**
     * Test register failure with invalid payload
     */
    public function testRegisterFailureWithInvalidPayload(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => '',
                'firstname' => '',
                'email' => 'invalid-email',
                'password' => 'short',
            ])
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterInternalServerError(): void
    {
        $client = self::createClient();

        $mockService = $this->createMock(RegistrationService::class);
        $mockService->method('registerEleve')->willThrowException(new \RuntimeException('Unexpected DB error'));

        static::getContainer()->set(RegistrationService::class, $mockService);

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test',
                'firstname' => 'User',
                'email' => 'test500@example.com',
                'password' => 'password123',
            ])
        );

        $this->assertResponseStatusCodeSame(500);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }
}
