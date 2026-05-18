<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class LoginTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests;

    public function testLoginSuccess(): void
    {
        $user = EleveFactory::createOne();

        $this->post('/api/login', [
            'email' => $user->getEmail(),
            'password' => 'password',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();

        $this->assertArrayHasKey('token', $responseData);
        $this->assertIsString($responseData['token']);
        $this->assertNotEmpty($responseData['token']);
    }

    public function testLoginFailureWithInvalidPassword(): void
    {
        EleveFactory::createOne([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->post('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginFailureWithNonExistentUser(): void
    {
        $this->post('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginWithMissingEmail(): void
    {
        $this->post('/api/login', ['password' => 'password123']);

        $this->assertResponseStatusCodeSame(400);

        $content = $this->getRequestResponse();
        $this->assertArrayHasKey('detail', $content);
        $this->assertSame('The key "email" must be provided.', $content["detail"]);
    }

    public function testLoginWithMissingPassword(): void
    {
        $this->post('/api/login', ['email' => 'test@example.com']);

        $this->assertResponseStatusCodeSame(400);

        $content = $this->getRequestResponse();
        $this->assertArrayHasKey('detail', $content);
        $this->assertSame('The key "password" must be provided.', $content["detail"]);
    }
}
