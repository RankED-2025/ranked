<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\BadgeCrudController;
use App\Controller\Admin\DashboardController;
use App\Entity\Badge;
use App\Factory\BadgeFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class BadgeCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

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

    // MakesHttpRequests creates a fresh test.client (shared=false) from the container,
    // which loses the admin session. Returning $this->client ensures the authenticated
    // client is reused for all HTTP helper calls.
    private function getCustomClient(): KernelBrowser
    {
        return $this->client;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function submitBadgeForm(string $url, string $type, string $label): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
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
    // Index (list)
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
        // Columns only render when at least one entity exists
        BadgeFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('type');
        $this->assertIndexColumnExists('label');
    }

    public function testIndexDisplaysExistingBadges(): void
    {
        BadgeFactory::createOne(['type' => 'or', 'label' => 'Expert']);

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Expert');
    }

    public function testIndexCountMatchesTotalBadges(): void
    {
        BadgeFactory::createMany(4);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(4);
    }

    public function testIndexShowsCreateAction(): void
    {
        $this->get($this->generateIndexUrl());

        $this->assertGlobalActionExists(Action::NEW);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $badge = BadgeFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists(Action::EDIT, $badge->getId());
        $this->assertIndexEntityActionExists(Action::DELETE, $badge->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $badge = BadgeFactory::createOne(['type' => 'platine', 'label' => 'Maître'])->_real();

        $this->get($this->generateDetailUrl($badge->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Maître');
        $this->assertSelectorTextContains('body', 'platine');
    }

    public function testDetailPageShowsEmptyProgressions(): void
    {
        $badge = BadgeFactory::createOne()->_real();

        $this->get($this->generateDetailUrl($badge->getId()));

        $this->assertResponseIsSuccessful();
        // Custom badge_progressions.html.twig renders this when there are no progressions
        $this->assertSelectorTextContains('body', 'Aucune progression');
    }

    public function testDetailPageReturns404ForNonExistentId(): void
    {
        $this->get($this->generateDetailUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function testCreateFormIsAccessible(): void
    {
        $this->get($this->generateNewFormUrl());

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('type');
        $this->assertFormFieldExists('label');
    }

    public function testCreateFormDoesNotShowProgressionsField(): void
    {
        $this->get($this->generateNewFormUrl());

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

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Badge Or Test');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $badge = BadgeFactory::createOne(['type' => 'bronze', 'label' => 'Débutant'])->_real();

        $this->get($this->generateEditFormUrl($badge->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('type');
        $this->assertFormFieldExists('label');
    }

    public function testEditFormPreFillsCurrentValues(): void
    {
        $badge = BadgeFactory::createOne(['type' => 'argent', 'label' => 'Intermédiaire'])->_real();

        $this->get($this->generateEditFormUrl($badge->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertInputValueSame('Badge[type]', 'argent');
        $this->assertInputValueSame('Badge[label]', 'Intermédiaire');
    }

    public function testEditPageShowsProgressionsSection(): void
    {
        $badge = BadgeFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($badge->getId()));

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
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Relation progressions (visible sur la page d'édition via badge_progressions.html.twig)
    // -------------------------------------------------------------------------

    public function testEditPageShowsProgressionsTableWhenProgressionsExist(): void
    {
        $badge = BadgeFactory::createOne()->_real();
        ProgressionFactory::createOne(['badge' => $badge, 'percentage' => 82]);

        $this->get($this->generateEditFormUrl($badge->getId()));

        $this->assertResponseIsSuccessful();
        // La table remplace le message "Aucune progression"
        $this->assertSelectorNotExists('em.text-muted');
        // Le pourcentage apparaît dans le corps de la table (3e colonne sans class dédiée)
        $this->assertSelectorTextContains('body', '82%');
    }

    public function testEditPageProgressionRowContainsEleveLink(): void
    {
        $badge = BadgeFactory::createOne()->_real();
        $eleve = EleveFactory::createOne(['firstname' => 'Lucas', 'name' => 'Bernard'])->_real();
        ProgressionFactory::createOne(['badge' => $badge, 'eleve' => $eleve]);

        $this->get($this->generateEditFormUrl($badge->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve/' . $eleve->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve/' . $eleve->getId() . '"]', 'Lucas Bernard');
    }

    public function testEditPageProgressionRowContainsCoursLink(): void
    {
        $badge = BadgeFactory::createOne()->_real();
        $cours = CoursFactory::createOne(['titre' => 'Trigonométrie'])->_real();
        ProgressionFactory::createOne(['badge' => $badge, 'cours' => $cours]);

        $this->get($this->generateEditFormUrl($badge->getId()));

        $this->assertSelectorExists('a[href$="/admin/cours/' . $cours->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/cours/' . $cours->getId() . '"]', 'Trigonométrie');
    }

    public function testEditPageProgressionRowContainsVoirLink(): void
    {
        $badge = BadgeFactory::createOne()->_real();
        $progression = ProgressionFactory::createOne(['badge' => $badge])->_real();

        $this->get($this->generateEditFormUrl($badge->getId()));

        $this->assertSelectorExists('a[href$="/admin/progression/' . $progression->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/progression/' . $progression->getId() . '"]', 'Voir');
    }

    // -------------------------------------------------------------------------
    // Delete
    //
    // EasyAdmin's delete expects form parameters (not JSON), so the delete POST
    // uses $this->client->request() directly to pass ['token' => $value] as
    // form data — MakesHttpRequests::post() encodes the body as JSON.
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteBadge(): void
    {
        $badge = BadgeFactory::createOne(['type' => 'bronze', 'label' => 'À Supprimer'])->_real();
        $badgeId = $badge->getId();

        // Extract CSRF token from the shared delete form on the index page
        $this->get($this->generateIndexUrl());
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

        $this->get($this->generateIndexUrl());
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
