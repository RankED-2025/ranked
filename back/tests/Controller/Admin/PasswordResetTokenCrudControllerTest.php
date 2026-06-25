<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\PasswordResetTokenCrudController;
use App\Entity\PasswordResetToken;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class PasswordResetTokenCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return PasswordResetTokenCrudController::class;
    }

    protected function getDashboardFqcn(): string
    {
        return DashboardController::class;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $admin = ProfesseurFactory::createOne(['roles' => ['ROLE_ADMIN']])->_real();
        $this->client->loginUser($admin, 'admin');
    }

    private function getCustomClient(): KernelBrowser
    {
        return $this->client;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createToken(): PasswordResetToken
    {
        $user = EleveFactory::createOne()->_real();
        $token = new PasswordResetToken($user, new \DateTimeImmutable('+1 hour'));
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function testUnauthenticatedAccessRedirectsToLogin(): void
    {
        $this->client->restart();
        $this->get($this->generateIndexUrl());

        $this->assertResponseRedirects('/login');
    }

    public function testNonAdminUserIsForbidden(): void
    {
        $this->client->restart();
        $user = ProfesseurFactory::createOne(['roles' => ['ROLE_PROFESSEUR']])->_real();
        $this->client->loginUser($user, 'admin');

        $this->get($this->generateIndexUrl());

        $this->assertResponseStatusCodeSame(403);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function testIndexIsAccessible(): void
    {
        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
    }

    public function testIndexShowsNoResultsWhenEmpty(): void
    {
        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(0);
    }

    public function testIndexHasExpectedColumns(): void
    {
        $this->createToken();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('userLink');
        $this->assertIndexColumnExists('token');
        $this->assertIndexColumnExists('expiresAt');
        $this->assertIndexColumnExists('used');
    }

    public function testIndexCountMatchesTotal(): void
    {
        $this->createToken();
        $this->createToken();

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(2);
    }

    public function testIndexShowsDeleteAction(): void
    {
        $token = $this->createToken();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists('delete', $token->getId());
    }

    // -------------------------------------------------------------------------
    // NEW et EDIT sont désactivés
    // -------------------------------------------------------------------------

    public function testIndexDoesNotShowNewButton(): void
    {
        $this->get($this->generateIndexUrl());

        // L'action NEW est désactivée → pas de bouton "Créer"
        $this->assertSelectorNotExists('a.action-new');
    }

    public function testIndexDoesNotShowEditActionPerRow(): void
    {
        $token = $this->createToken();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionNotExists('edit', $token->getId());
    }

    // -------------------------------------------------------------------------
    // Detail (action DETAIL ajoutée explicitement dans configureActions)
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $token = $this->createToken();

        $this->get($this->generateDetailUrl($token->getId()));

        $this->assertResponseIsSuccessful();
    }

    public function testDetailPageShowsTokenValue(): void
    {
        $token = $this->createToken();
        $tokenValue = $token->getToken();

        $this->get($this->generateDetailUrl($token->getId()));

        $this->assertSelectorTextContains('body', $tokenValue);
    }

    public function testDetailPageReturns404ForNonExistentId(): void
    {
        $this->get($this->generateDetailUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteToken(): void
    {
        $token = $this->createToken();
        $tokenId = $token->getId();

        $this->get($this->generateIndexUrl());
        $csrfToken = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/password-reset-token/' . $tokenId . '/delete', ['token' => $csrfToken]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(PasswordResetToken::class, $tokenId));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $token = $this->createToken();
        $tokenId = $token->getId();

        $this->client->request('POST', '/admin/password-reset-token/' . $tokenId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(PasswordResetToken::class, $tokenId));
    }

    // -------------------------------------------------------------------------
    // Relations (userLink template)
    // -------------------------------------------------------------------------

    public function testIndexUserLinkPointsToCorrectUserDetailPage(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        $token = new PasswordResetToken($eleve, new \DateTimeImmutable('+1 hour'));
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="userLink"] a')->attr('href');
        $this->assertStringEndsWith('/admin/eleve/' . $eleve->getId(), $href);
    }
}
