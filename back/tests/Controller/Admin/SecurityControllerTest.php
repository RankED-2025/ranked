<?php

namespace App\Tests\Controller\Admin;

use App\Entity\AdminSsoToken;
use App\Factory\ProfesseurFactory;
use App\Service\AdminSsoService;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class SecurityControllerTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, GetsContainerServices;

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

    public function testConsumeValidTokenLogsInAdminAndRedirectsToAdmin(): void
    {
        $admin = ProfesseurFactory::createOne(['roles' => ['ROLE_ADMIN']])->_real();
        $token = $this->getService(AdminSsoService::class)->createSsoUrl($admin);

        $this->get('/admin/sso/' . $token, ['Content-Type' => null]);

        $this->assertResponseRedirects('/admin');

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
    }

    public function testConsumeInvalidTokenRedirectsToLogin(): void
    {
        $this->get('/admin/sso/does-not-exist', ['Content-Type' => null]);

        $this->assertResponseRedirects('/login');
    }

    public function testConsumeExpiredTokenRedirectsToLogin(): void
    {
        $admin = ProfesseurFactory::createOne(['roles' => ['ROLE_ADMIN']])->_real();

        $em = $this->getService(EntityManagerInterface::class);
        $expiredToken = new AdminSsoToken($admin, new \DateTimeImmutable('-1 second'));
        $em->persist($expiredToken);
        $em->flush();

        $this->get('/admin/sso/' . $expiredToken->getToken(), ['Content-Type' => null]);

        $this->assertResponseRedirects('/login');
    }

    public function testConsumeAlreadyUsedTokenRedirectsToLogin(): void
    {
        $admin = ProfesseurFactory::createOne(['roles' => ['ROLE_ADMIN']])->_real();
        $token = $this->getService(AdminSsoService::class)->createSsoUrl($admin);

        $this->get('/admin/sso/' . $token, ['Content-Type' => null]);
        $this->assertResponseRedirects('/admin');

        $this->client->restart();

        $this->get('/admin/sso/' . $token, ['Content-Type' => null]);

        $this->assertResponseRedirects('/login');
    }

    public function testConsumeTokenForUserWhoLostAdminRoleRedirectsToLogin(): void
    {
        $admin = ProfesseurFactory::createOne(['roles' => ['ROLE_ADMIN']])->_real();
        $token = $this->getService(AdminSsoService::class)->createSsoUrl($admin);

        $em = $this->getService(EntityManagerInterface::class);
        $admin->setRoles(['ROLE_PROFESSEUR']);
        $em->flush();

        $this->get('/admin/sso/' . $token, ['Content-Type' => null]);

        $this->assertResponseRedirects('/login');
    }
}
