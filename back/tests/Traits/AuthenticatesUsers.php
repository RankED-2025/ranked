<?php

namespace App\Tests\Traits;

trait AuthenticatesUsers
{
    use MakesHttpRequests;

    private function authenticateAndGetToken(string $email, string $password): string
    {
        $this->post('/api/login', ['email' => $email, 'password' => $password]);

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($this->getCustomClient()->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);

        return $responseData['token'];
    }
}
