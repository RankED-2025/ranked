<?php

namespace App\Tests\Controller\Auth;

use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class AdminSsoControllerTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    public function testCreateSsoLinkRequiresAuthentication(): void
    {
        $this->post('/api/admin/sso');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateSsoLinkForbiddenForNonAdmin(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->post('/api/admin/sso', null, $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateSsoLinkReturnsUrlForAdmin(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'admin@example.com',
            'password' => 'password123',
            'roles' => ['ROLE_ADMIN'],
        ]);
        $token = $this->authenticateAndGetToken('admin@example.com', 'password123');

        $this->post('/api/admin/sso', null, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $data = $this->getRequestResponse();

        $this->assertArrayHasKey('url', $data);
        $this->assertStringContainsString('/admin/sso/', $data['url']);
    }
}
