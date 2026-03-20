<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
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
}
