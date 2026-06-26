<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Service\RegistrationService;
use App\Tests\Traits\MakesHttpRequests;
use App\Tests\Traits\SetsContainerServices;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class RegisterTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, SetsContainerServices;

    public function testRegisterSuccess(): void
    {
        $this->post('/api/register', [
            'name' => 'Doe',
            'firstname' => 'John',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $responseData = $this->getRequestResponse();
        $this->assertArrayHasKey('token', $responseData);
        $this->assertIsString($responseData['token']);
        $this->assertNotEmpty($responseData['token']);
    }

    public function testRegisterFailureWithExistingEmail(): void
    {
        EleveFactory::createOne([
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        $this->post('/api/register', [
            'name' => 'Dupont',
            'firstname' => 'Jean',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testRegisterFailureWithInvalidPayload(): void
    {
        $this->post('/api/register', [
            'name' => '',
            'firstname' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterInternalServerError(): void
    {
        // Load the Kernel
        $this->getCustomClient();

        $mockService = $this->createMock(RegistrationService::class);
        $mockService
            ->method('registerEleve')
            ->willThrowException(new \RuntimeException('Unexpected DB error'));

        $this->setService(RegistrationService::class, $mockService);

        $this->post('/api/register', [
            'name' => 'Test',
            'firstname' => 'User',
            'email' => 'test500@example.com',
            'password' => 'password123',
        ]);

        $this->assertResponseStatusCodeSame(500);

        $responseData = $this->getRequestResponse();
        $this->assertArrayHasKey('error', $responseData);
    }
}
