<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\BadgeCrudController;
use App\Controller\Admin\DashboardController;
use App\Entity\Badge;
use App\Factory\BadgeFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class BadgeCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;

    protected function getControllerFqcn(): string
    {
        return BadgeCrudController::class;
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

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function submitBadgeForm(string $url, string $type, string $label): void
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[method="post"]')->form([
            'Badge[type]'  => $type,
            'Badge[label]' => $label,
        ]);
        $this->client->submit($form);
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function testUnauthenticatedAccessRedirectsToLogin(): void
    {
        $this->client->restart();
        $this->client->request('GET', $this->generateIndexUrl());

        $this->assertResponseRedirects('/login');
    }

    public function testNonAdminUserIsForbidden(): void
    {
        $this->client->restart();
        $user = ProfesseurFactory::createOne(['roles' => ['ROLE_PROFESSEUR']])->_real();
        $this->client->loginUser($user, 'admin');

        $this->client->request('GET', $this->generateIndexUrl());

        $this->assertResponseStatusCodeSame(403);
    }

    // -------------------------------------------------------------------------
    // Index (list)
    // -------------------------------------------------------------------------

    public function testIndexIsAccessible(): void
    {
        $this->client->request('GET', $this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
    }

    public function testIndexShowsNoResultsWhenEmpty(): void
    {
        $this->client->request('GET', $this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(0);
    }

    public function testIndexHasExpectedColumns(): void
    {
        // Columns only render when at least one entity exists
        BadgeFactory::createOne();

        $this->client->request('GET', $this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('type');
        $this->assertIndexColumnExists('label');
    }

    public function testIndexDisplaysExistingBadges(): void
    {
        BadgeFactory::createOne(['type' => 'or', 'label' => 'Expert']);

        $this->client->request('GET', $this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Expert');
    }

    public function testIndexCountMatchesTotalBadges(): void
    {
        BadgeFactory::createMany(4);

        $this->client->request('GET', $this->generateIndexUrl());

        $this->assertIndexFullEntityCount(4);
    }

    public function testIndexShowsCreateActionsInPage()
    {
        $this->client->request('GET', $this->generateIndexUrl());
        $this->assertGlobalActionExists('new');
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $badge = BadgeFactory::createOne()->_real();

        $this->client->request('GET', $this->generateIndexUrl());

        $this->assertIndexEntityActionExists('edit', $badge->getId());
        $this->assertIndexEntityActionExists('delete', $badge->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $badge = BadgeFactory::createOne(['type' => 'platine', 'label' => 'Maître'])->_real();

        $this->client->request('GET', $this->generateDetailUrl($badge->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Maître');
        $this->assertSelectorTextContains('body', 'platine');
    }

    public function testDetailPageShowsEmptyProgressions(): void
    {
        $badge = BadgeFactory::createOne()->_real();

        $this->client->request('GET', $this->generateDetailUrl($badge->getId()));

        $this->assertResponseIsSuccessful();
        // Custom badge_progressions.html.twig renders this when there are no progressions
        $this->assertSelectorTextContains('body', 'Aucune progression');
    }

    public function testDetailPageReturns404ForNonExistentId(): void
    {
        $this->client->request('GET', $this->generateDetailUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function testCreateFormIsAccessible(): void
    {
        $this->client->request('GET', $this->generateNewFormUrl());

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('type');
        $this->assertFormFieldExists('label');
    }

    public function testCreateFormDoesNotShowProgressionsField(): void
    {
        $this->client->request('GET', $this->generateNewFormUrl());

        $this->assertResponseIsSuccessful();
        // progressionsView uses a custom non-standard display type (not a real form input)
        // and is hidden on create via hideWhenCreating()
        $this->assertSelectorNotExists('#Badge_progressionsView');
    }

    public function testAdminCanCreateBadge(): void
    {
        $this->client->followRedirects();
        $this->submitBadgeForm($this->generateNewFormUrl(), 'diamant', 'Badge Diamant Test');

        $this->assertResponseIsSuccessful();

        $this->entityManager->clear();
        $badge = $this->entityManager->getRepository(Badge::class)->findOneBy(['label' => 'Badge Diamant Test']);
        $this->assertNotNull($badge);
        $this->assertSame('diamant', $badge->getType());
    }

    public function testCreatedBadgeAppearsInList(): void
    {
        $this->client->followRedirects();
        $this->submitBadgeForm($this->generateNewFormUrl(), 'or', 'Badge Or Test');

        $this->client->request('GET', $this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Badge Or Test');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $badge = BadgeFactory::createOne(['type' => 'bronze', 'label' => 'Débutant'])->_real();

        $this->client->request('GET', $this->generateEditFormUrl($badge->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('type');
        $this->assertFormFieldExists('label');
    }

    public function testEditFormPreFillsCurrentValues(): void
    {
        $badge = BadgeFactory::createOne(['type' => 'argent', 'label' => 'Intermédiaire'])->_real();

        $this->client->request('GET', $this->generateEditFormUrl($badge->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertInputValueSame('Badge[type]', 'argent');
        $this->assertInputValueSame('Badge[label]', 'Intermédiaire');
    }

    public function testEditPageShowsProgressionsSection(): void
    {
        $badge = BadgeFactory::createOne()->_real();

        $this->client->request('GET', $this->generateEditFormUrl($badge->getId()));

        // progressionsView is visible on edit (only hidden when creating)
        // Rendered via badge_progressions.html.twig as a read-only table
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Aucune progression');
    }

    public function testAdminCanEditBadge(): void
    {
        $this->client->followRedirects();
        $badge = BadgeFactory::createOne(['type' => 'bronze', 'label' => 'Débutant'])->_real();

        $this->submitBadgeForm($this->generateEditFormUrl($badge->getId()), 'or', 'Avancé Modifié');

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Badge::class, $badge->getId());
        $this->assertNotNull($updated);
        $this->assertSame('or', $updated->getType());
        $this->assertSame('Avancé Modifié', $updated->getLabel());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->client->request('GET', $this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteBadge(): void
    {
        $badge = BadgeFactory::createOne(['type' => 'bronze', 'label' => 'À Supprimer'])->_real();
        $badgeId = $badge->getId();

        // Extract CSRF token from the shared delete form on the index page
        $this->client->request('GET', $this->generateIndexUrl());
        $this->assertResponseIsSuccessful();
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/badge/' . $badgeId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Badge::class, $badgeId));
    }

    public function testDeleteReducesBadgeCount(): void
    {
        BadgeFactory::createMany(3);
        $toDelete = BadgeFactory::createOne()->_real();

        $this->client->request('GET', $this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/badge/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(3, $this->entityManager->getRepository(Badge::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDeleteBadge(): void
    {
        $badge = BadgeFactory::createOne()->_real();
        $badgeId = $badge->getId();

        // EasyAdmin redirects even on bad CSRF token but does NOT delete the entity
        $this->client->request('POST', '/admin/badge/' . $badgeId . '/delete', ['token' => 'invalid-token']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Badge::class, $badgeId));
    }
}
