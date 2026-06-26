<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Repository\PasswordResetTokenRepository;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class PasswordResetTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, GetsContainerServices;

    public function testRequestWithInvalidPayload(): void
    {
        $this->post('/api/password-reset/request', ['email' => 'not-an-email']);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRequestWithUnknownEmailStillSucceeds(): void
    {
        $this->post('/api/password-reset/request', ['email' => 'unknown@example.com']);

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertArrayHasKey('message', $responseData);
    }

    public function testConfirmWithInvalidPayload(): void
    {
        $this->post('/api/password-reset/confirm', ['token' => '', 'password' => 'short']);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testConfirmWithInvalidToken(): void
    {
        $this->post('/api/password-reset/confirm', [
            'token' => 'invalid-token',
            'password' => 'newPassword123',
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRequestWithKnownEmailSendsReset(): void
    {
        EleveFactory::createOne([
            'email' => 'known.user@example.com',
            'password' => 'password123',
        ]);

        $this->post('/api/password-reset/request', ['email' => 'known.user@example.com']);

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertArrayHasKey('message', $responseData);
    }

    public function testConfirmWithValidToken(): void
    {
        EleveFactory::createOne([
            'email' => 'reset.user@example.com',
            'password' => 'oldPassword123',
        ]);

        $this->post('/api/password-reset/request', ['email' => 'reset.user@example.com']);

        $token = $this->getService(PasswordResetTokenRepository::class)->findOneBy([], ['id' => 'DESC']);

        $this->assertNotNull($token);

        $this->post('/api/password-reset/confirm', [
            'token' => $token->getToken(),
            'password' => 'newPassword123',
        ]);

        $this->assertResponseStatusCodeSame(200);
    }
}
