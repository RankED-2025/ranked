<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class LogoutTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    public function testLogoutWithoutAuthentication(): void
    {
        $this->post('/api/logout', ['refresh_token' => 'any']);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLogoutWithMissingRefreshToken(): void
    {
        EleveFactory::createOne([
            'email' => 'logout.user@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('logout.user@example.com', 'password123');

        $this->post('/api/logout', [], $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLogoutWithInvalidRefreshToken(): void
    {
        EleveFactory::createOne([
            'email' => 'logout.user2@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('logout.user2@example.com', 'password123');

        $this->post('/api/logout', ['refresh_token' => 'invalid-token'], $this->withToken($token));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testLogoutSuccess(): void
    {
        EleveFactory::createOne([
            'email' => 'logout.success@example.com',
            'password' => 'password123',
        ]);

        $this->post('/api/login', [
            'email' => 'logout.success@example.com',
            'password' => 'password123',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $loginData = json_decode($this->getCustomClient()->getResponse()->getContent(), true);
        $jwtToken = $loginData['token'];
        $refreshToken = $loginData['refresh_token'];

        $this->post('/api/logout', ['refresh_token' => $refreshToken], $this->withToken($jwtToken));

        $this->assertResponseStatusCodeSame(204);
    }
}
