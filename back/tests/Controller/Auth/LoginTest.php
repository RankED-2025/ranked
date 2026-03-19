<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class LoginTest extends WebTestCase
{
    use ResetDatabase;

    /**
     * Test successful login with valid credentials
     */
    public function testLoginSuccess(): void
    {
        $client = self::createClient();
        $user = EleveFactory::createOne();

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $user->getEmail(),
                'password' => 'password',
            ])
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertIsString($responseData['token']);
        $this->assertNotEmpty($responseData['token']);
    }

    /**
     * Test login failure with invalid password
     */
    public function testLoginFailureWithInvalidPassword(): void
    {
        $client = self::createClient();
        $user = EleveFactory::createOne([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $user->getEmail(),
                'password' => 'wrongpassword',
            ])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Test login failure with non-existent user
     */
    public function testLoginFailureWithNonExistentUser(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'nonexistent@example.com',
                'password' => 'password123',
            ])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Test login with missing email field
     */
    public function testLoginWithMissingEmail(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'password' => 'password123',
            ])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * Test login with missing password field
     */
    public function testLoginWithMissingPassword(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@example.com',
            ])
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
