<?php

namespace App\Tests\Controller\Admin;

use App\Factory\ProfesseurFactory;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class DashboardControllerTest extends WebTestCase
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

    public function testDashboardShowsBackToSiteLink(): void
    {
        $admin = ProfesseurFactory::createOne(['roles' => ['ROLE_ADMIN']])->_real();
        $this->client->loginUser($admin, 'admin');

        $this->get('/admin', ['Content-Type' => null]);

        $this->assertResponseIsSuccessful();

        $content = $this->getResponseContent();

        $expectedUrl = rtrim($_SERVER['FRONTEND_URL'], '/') . '/';

        $this->assertStringContainsString('Retour au site', $content);
        $this->assertStringContainsString('href="' . $expectedUrl . '"', $content);
    }
}
