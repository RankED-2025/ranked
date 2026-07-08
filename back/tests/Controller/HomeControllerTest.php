<?php

namespace App\Tests\Controller;

use App\Factory\ProfesseurFactory;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class HomeControllerTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    private function getCustomClient(): KernelBrowser
    {
        return $this->client;
    }

    public function testHomeReturnsJsonWhenRequestContentTypeIsJson(): void
    {
        $this->get('/');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = $this->getRequestResponse();

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('status', $data);
    }

    public function testHomeReturnsHtmlPageWithLoginLinkWhenAnonymous(): void
    {
        $this->get('/', ['Content-Type' => null]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('text/html', (string) static::getClient()->getResponse()->headers->get('Content-Type'));

        $content = $this->getResponseContent();

        $this->assertStringContainsString('Se connecter', $content);
        $this->assertStringNotContainsString('Panel admin', $content);
    }

    public function testHomeShowsAdminPanelLinkForLoggedInAdmin(): void
    {
        $admin = ProfesseurFactory::createOne(['roles' => ['ROLE_ADMIN']])->_real();

        $this->client->loginUser($admin, 'admin');

        $this->get('/', ['Content-Type' => null]);

        $this->assertResponseIsSuccessful();

        $content = $this->getResponseContent();

        $this->assertStringContainsString('Panel admin', $content);
        $this->assertStringContainsString('Se déconnecter', $content);
        $this->assertStringNotContainsString('Se connecter', $content);
    }

    public function testHomeShowsLogoutLinkForLoggedInNonAdmin(): void
    {
        $professeur = ProfesseurFactory::createOne(['roles' => ['ROLE_PROFESSEUR']])->_real();

        $this->client->loginUser($professeur, 'admin');

        $this->get('/', ['Content-Type' => null]);

        $this->assertResponseIsSuccessful();

        $content = $this->getResponseContent();

        $this->assertStringContainsString('Se déconnecter', $content);
        $this->assertStringNotContainsString('Panel admin', $content);
    }
}
