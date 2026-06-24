<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\DifficulteCrudController;
use App\Entity\Difficulte;
use App\Factory\DifficulteFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class DifficulteCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return DifficulteCrudController::class;
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

    private function submitDifficulteForm(string $url, string $label): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Difficulte[label]' => $label,
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
        DifficulteFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('label');
    }

    public function testIndexDisplaysExistingDifficultes(): void
    {
        DifficulteFactory::createOne(['label' => 'Facile']);

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Facile');
    }

    public function testIndexCountMatchesTotalDifficultes(): void
    {
        DifficulteFactory::createMany(5);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(5);
    }

    public function testIndexShowsCreateAction(): void
    {
        $this->get($this->generateIndexUrl());

        $this->assertGlobalActionExists('new');
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $difficulte = DifficulteFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists('edit', $difficulte->getId());
        $this->assertIndexEntityActionExists('delete', $difficulte->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $difficulte = DifficulteFactory::createOne(['label' => 'Expert'])->_real();

        $this->get($this->generateDetailUrl($difficulte->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Expert');
    }

    public function testDetailPageShowsEmptyCoursList(): void
    {
        $difficulte = DifficulteFactory::createOne()->_real();

        $this->get($this->generateDetailUrl($difficulte->getId()));

        $this->assertResponseIsSuccessful();
        // difficulte_cours.html.twig renders this when there are no cours
        $this->assertSelectorTextContains('body', 'Aucun cours');
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
        $this->assertFormFieldExists('label');
    }

    public function testCreateFormDoesNotShowCoursField(): void
    {
        $this->get($this->generateNewFormUrl());

        $this->assertResponseIsSuccessful();
        // coursView is a custom display field hidden on create via hideWhenCreating()
        $this->assertSelectorNotExists('#Difficulte_coursView');
    }

    public function testAdminCanCreateDifficulte(): void
    {
        $this->client->followRedirects();
        $this->submitDifficulteForm($this->generateNewFormUrl(), 'Intermédiaire');

        $this->assertResponseIsSuccessful();

        $this->entityManager->clear();
        $difficulte = $this->entityManager->getRepository(Difficulte::class)->findOneBy(['label' => 'Intermédiaire']);
        $this->assertNotNull($difficulte);
    }

    public function testCreatedDifficulteAppearsInList(): void
    {
        $this->client->followRedirects();
        $this->submitDifficulteForm($this->generateNewFormUrl(), 'Avancé');

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Avancé');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $difficulte = DifficulteFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($difficulte->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('label');
    }

    public function testEditFormPreFillsCurrentValue(): void
    {
        $difficulte = DifficulteFactory::createOne(['label' => 'Moyen'])->_real();

        $this->get($this->generateEditFormUrl($difficulte->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertInputValueSame('Difficulte[label]', 'Moyen');
    }

    public function testEditPageShowsCoursSection(): void
    {
        $difficulte = DifficulteFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($difficulte->getId()));

        // coursView is visible on edit, rendered via difficulte_cours.html.twig
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Aucun cours');
    }

    public function testAdminCanEditDifficulte(): void
    {
        $this->client->followRedirects();
        $difficulte = DifficulteFactory::createOne(['label' => 'Facile'])->_real();

        $this->submitDifficulteForm($this->generateEditFormUrl($difficulte->getId()), 'Très Facile');

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Difficulte::class, $difficulte->getId());
        $this->assertNotNull($updated);
        $this->assertSame('Très Facile', $updated->getLabel());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    //
    // EasyAdmin's delete expects form parameters (not JSON), so the delete POST
    // uses $this->client->request() directly to pass ['token' => $value] as
    // form data — MakesHttpRequests::post() encodes the body as JSON.
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteDifficulte(): void
    {
        $difficulte = DifficulteFactory::createOne(['label' => 'À Supprimer'])->_real();
        $difficulteId = $difficulte->getId();

        $this->get($this->generateIndexUrl());
        $this->assertResponseIsSuccessful();
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/difficulte/' . $difficulteId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Difficulte::class, $difficulteId));
    }

    public function testDeleteReducesDifficulteCount(): void
    {
        DifficulteFactory::createMany(3);
        $toDelete = DifficulteFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/difficulte/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(3, $this->entityManager->getRepository(Difficulte::class)->count());
    }

    public function testDeleteWithInvalidTokenDoesNotDeleteDifficulte(): void
    {
        $difficulte = DifficulteFactory::createOne()->_real();
        $difficulteId = $difficulte->getId();

        // EasyAdmin redirects even on bad CSRF token but does NOT delete the entity
        $this->client->request('POST', '/admin/difficulte/' . $difficulteId . '/delete', ['token' => 'invalid-token']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Difficulte::class, $difficulteId));
    }
}
