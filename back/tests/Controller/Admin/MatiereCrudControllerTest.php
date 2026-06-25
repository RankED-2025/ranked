<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\MatiereCrudController;
use App\Entity\Matiere;
use App\Factory\CoursFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class MatiereCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return MatiereCrudController::class;
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

    private function submitMatiereForm(string $url, string $libelle): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Matiere[libelle]' => $libelle,
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
        MatiereFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('libelle');
    }

    public function testIndexDisplaysExistingMatieres(): void
    {
        MatiereFactory::createOne(['libelle' => 'Mathématiques']);

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Mathématiques');
    }

    public function testIndexCountMatchesTotalMatieres(): void
    {
        MatiereFactory::createMany(5);

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
        $matiere = MatiereFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists('edit', $matiere->getId());
        $this->assertIndexEntityActionExists('delete', $matiere->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $matiere = MatiereFactory::createOne(['libelle' => 'Physique-Chimie'])->_real();

        $this->get($this->generateDetailUrl($matiere->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Physique-Chimie');
    }

    public function testDetailPageShowsEmptyCoursList(): void
    {
        $matiere = MatiereFactory::createOne()->_real();

        $this->get($this->generateDetailUrl($matiere->getId()));

        $this->assertResponseIsSuccessful();
        // matiere_cours.html.twig renders this when there are no cours
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
        $this->assertFormFieldExists('libelle');
    }

    public function testCreateFormDoesNotShowCoursField(): void
    {
        $this->get($this->generateNewFormUrl());

        $this->assertResponseIsSuccessful();
        // coursView is a custom display field hidden on create via hideWhenCreating()
        $this->assertSelectorNotExists('#Matiere_coursView');
    }

    public function testAdminCanCreateMatiere(): void
    {
        $this->client->followRedirects();
        $this->submitMatiereForm($this->generateNewFormUrl(), 'Informatique');

        $this->assertResponseIsSuccessful();

        $this->entityManager->clear();
        $matiere = $this->entityManager->getRepository(Matiere::class)->findOneBy(['libelle' => 'Informatique']);
        $this->assertNotNull($matiere);
    }

    public function testCreatedMatiereAppearsInList(): void
    {
        $this->client->followRedirects();
        $this->submitMatiereForm($this->generateNewFormUrl(), 'Philosophie');

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Philosophie');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $matiere = MatiereFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($matiere->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('libelle');
    }

    public function testEditFormPreFillsCurrentValue(): void
    {
        $matiere = MatiereFactory::createOne(['libelle' => 'Français'])->_real();

        $this->get($this->generateEditFormUrl($matiere->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertInputValueSame('Matiere[libelle]', 'Français');
    }

    public function testEditPageShowsCoursSection(): void
    {
        $matiere = MatiereFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($matiere->getId()));

        // coursView is visible on edit, rendered via matiere_cours.html.twig
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Aucun cours');
    }

    public function testAdminCanEditMatiere(): void
    {
        $this->client->followRedirects();
        $matiere = MatiereFactory::createOne(['libelle' => 'Histoire'])->_real();

        $this->submitMatiereForm($this->generateEditFormUrl($matiere->getId()), 'Histoire-Géographie');

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Matiere::class, $matiere->getId());
        $this->assertNotNull($updated);
        $this->assertSame('Histoire-Géographie', $updated->getLibelle());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Relation cours (visible sur la page d'édition via matiere_cours.html.twig)
    // -------------------------------------------------------------------------

    public function testEditPageShowsCoursTableWhenCoursExist(): void
    {
        $matiere = MatiereFactory::createOne()->_real();
        CoursFactory::createOne(['matiere' => $matiere, 'titre' => 'Algèbre linéaire']);

        $this->get($this->generateEditFormUrl($matiere->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table tbody td', 'Algèbre linéaire');
        $this->assertSelectorNotExists('em.text-muted');
    }

    public function testEditPageCoursRowContainsProfesseurLink(): void
    {
        $prof = ProfesseurFactory::createOne(['firstname' => 'Alice', 'name' => 'Martin'])->_real();
        $matiere = MatiereFactory::createOne()->_real();
        CoursFactory::createOne(['matiere' => $matiere, 'professeur' => $prof]);

        $this->get($this->generateEditFormUrl($matiere->getId()));

        $this->assertSelectorExists('a[href$="/admin/professeur/' . $prof->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/professeur/' . $prof->getId() . '"]', 'Alice Martin');
    }

    public function testEditPageCoursRowContainsVoirLinkToCours(): void
    {
        $matiere = MatiereFactory::createOne()->_real();
        $cours = CoursFactory::createOne(['matiere' => $matiere])->_real();

        $this->get($this->generateEditFormUrl($matiere->getId()));

        $this->assertSelectorExists('a[href$="/admin/cours/' . $cours->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/cours/' . $cours->getId() . '"]', 'Voir');
    }

    // -------------------------------------------------------------------------
    // Delete
    //
    // EasyAdmin's delete expects form parameters (not JSON), so the delete POST
    // uses $this->client->request() directly to pass ['token' => $value] as
    // form data — MakesHttpRequests::post() encodes the body as JSON.
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteMatiere(): void
    {
        $matiere = MatiereFactory::createOne(['libelle' => 'À Supprimer'])->_real();
        $matiereId = $matiere->getId();

        $this->get($this->generateIndexUrl());
        $this->assertResponseIsSuccessful();
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/matiere/' . $matiereId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Matiere::class, $matiereId));
    }

    public function testDeleteReducesMatiereCount(): void
    {
        MatiereFactory::createMany(3);
        $toDelete = MatiereFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/matiere/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(3, $this->entityManager->getRepository(Matiere::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDeleteMatiere(): void
    {
        $matiere = MatiereFactory::createOne()->_real();
        $matiereId = $matiere->getId();

        // EasyAdmin redirects even on bad CSRF token but does NOT delete the entity
        $this->client->request('POST', '/admin/matiere/' . $matiereId . '/delete', ['token' => 'invalid-token']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Matiere::class, $matiereId));
    }
}
