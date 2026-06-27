<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ClasseCrudController;
use App\Controller\Admin\DashboardController;
use App\Entity\Classe;
use App\Factory\ClasseFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class ClasseCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return ClasseCrudController::class;
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

    private function submitClasseForm(string $url, string $nom, int $professeurId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Classe[nom]'        => $nom,
            'Classe[professeur]' => (string) $professeurId,
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
        ClasseFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('nom');
        $this->assertIndexColumnExists('professeur');
    }

    public function testIndexDisplaysExistingClasses(): void
    {
        ClasseFactory::createOne(['nom' => '6ème A']);

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', '6ème A');
    }

    public function testIndexCountMatchesTotalClasses(): void
    {
        ClasseFactory::createMany(5);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(5);
    }

    public function testIndexShowsCreateAction(): void
    {
        $this->get($this->generateIndexUrl());

        $this->assertGlobalActionExists(Action::NEW);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $classe = ClasseFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists(Action::EDIT, $classe->getId());
        $this->assertIndexEntityActionExists(Action::DELETE, $classe->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $classe = ClasseFactory::createOne(['nom' => '5ème B'])->_real();

        $this->get($this->generateDetailUrl($classe->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '5ème B');
    }

    public function testDetailPageShowsEmptyElevesList(): void
    {
        $classe = ClasseFactory::createOne()->_real();

        $this->get($this->generateDetailUrl($classe->getId()));

        $this->assertResponseIsSuccessful();
        // classe_eleves.html.twig renders this when there are no eleves
        $this->assertSelectorTextContains('body', 'Aucun élève');
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
        $this->assertFormFieldExists('nom');
        $this->assertFormFieldExists('professeur');
    }

    public function testCreateFormDoesNotShowElevesField(): void
    {
        $this->get($this->generateNewFormUrl());

        $this->assertResponseIsSuccessful();
        // elevesView is a custom display field hidden on create via hideWhenCreating()
        $this->assertSelectorNotExists('#Classe_elevesView');
    }

    public function testAdminCanCreateClasse(): void
    {
        $this->client->followRedirects();
        $professeur = ProfesseurFactory::createOne()->_real();

        $this->submitClasseForm($this->generateNewFormUrl(), '4ème C', $professeur->getId());

        $this->assertResponseIsSuccessful();

        $this->entityManager->clear();
        $classe = $this->entityManager->getRepository(Classe::class)->findOneBy(['nom' => '4ème C']);
        $this->assertNotNull($classe);
        $this->assertSame($professeur->getId(), $classe->getProfesseur()->getId());
    }

    public function testCreatedClasseAppearsInList(): void
    {
        $this->client->followRedirects();
        $professeur = ProfesseurFactory::createOne()->_real();

        $this->submitClasseForm($this->generateNewFormUrl(), '3ème D', $professeur->getId());

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', '3ème D');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $classe = ClasseFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($classe->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('nom');
        $this->assertFormFieldExists('professeur');
    }

    public function testEditFormPreFillsCurrentValues(): void
    {
        $classe = ClasseFactory::createOne(['nom' => '6ème B'])->_real();

        $this->get($this->generateEditFormUrl($classe->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertInputValueSame('Classe[nom]', '6ème B');
    }

    public function testEditPageShowsElevesSection(): void
    {
        $classe = ClasseFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($classe->getId()));

        // elevesView is visible on edit, rendered via classe_eleves.html.twig
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Aucun élève');
    }

    public function testAdminCanEditClasseNom(): void
    {
        $this->client->followRedirects();
        $classe = ClasseFactory::createOne(['nom' => '5ème A'])->_real();

        $this->submitClasseForm(
            $this->generateEditFormUrl($classe->getId()),
            '5ème A Modifiée',
            $classe->getProfesseur()->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Classe::class, $classe->getId());
        $this->assertNotNull($updated);
        $this->assertSame('5ème A Modifiée', $updated->getNom());
    }

    public function testAdminCanChangeProfesseurOnClasse(): void
    {
        $this->client->followRedirects();
        $classe = ClasseFactory::createOne(['nom' => '4ème B'])->_real();
        $newProfesseur = ProfesseurFactory::createOne()->_real();

        $this->submitClasseForm(
            $this->generateEditFormUrl($classe->getId()),
            '4ème B',
            $newProfesseur->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Classe::class, $classe->getId());
        $this->assertNotNull($updated);
        $this->assertSame($newProfesseur->getId(), $updated->getProfesseur()->getId());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Relation professeur
    // -------------------------------------------------------------------------

    public function testIndexProfesseurAppearsAsLink(): void
    {
        ClasseFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertSelectorExists('td[data-column="professeur"] a');
    }

    public function testIndexProfesseurLinkPointsToCorrectDetailPage(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();
        ClasseFactory::createOne(['professeur' => $prof]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()
            ->filter('td[data-column="professeur"] a')
            ->attr('href');

        $this->assertStringEndsWith('/admin/professeur/' . $prof->getId(), $href);
    }

    public function testIndexProfesseurLinkDisplaysProfesseurName(): void
    {
        $prof = ProfesseurFactory::createOne(['firstname' => 'Jean', 'name' => 'Dupont'])->_real();
        ClasseFactory::createOne(['professeur' => $prof]);

        $this->get($this->generateIndexUrl());

        $this->assertSelectorTextContains('td[data-column="professeur"] a', 'Jean Dupont');
    }

    public function testDetailProfesseurAppearsAsLink(): void
    {
        $classe = ClasseFactory::createOne()->_real();

        $this->get($this->generateDetailUrl($classe->getId()));

        $this->assertSelectorExists('.field-group.field-association .field-value a');
    }

    public function testDetailProfesseurLinkPointsToCorrectDetailPage(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();
        $classe = ClasseFactory::createOne(['professeur' => $prof])->_real();

        $this->get($this->generateDetailUrl($classe->getId()));

        $href = $this->client->getCrawler()
            ->filter('.field-group.field-association .field-value a')
            ->attr('href');

        $this->assertStringEndsWith('/admin/professeur/' . $prof->getId(), $href);
    }

    public function testDetailProfesseurLinkDisplaysProfesseurName(): void
    {
        $prof = ProfesseurFactory::createOne(['firstname' => 'Marie', 'name' => 'Curie'])->_real();
        $classe = ClasseFactory::createOne(['professeur' => $prof])->_real();

        $this->get($this->generateDetailUrl($classe->getId()));

        $this->assertSelectorTextContains('.field-group.field-association .field-value a', 'Marie Curie');
    }

    // -------------------------------------------------------------------------
    // Delete
    //
    // EasyAdmin's delete expects form parameters (not JSON), so the delete POST
    // uses $this->client->request() directly to pass ['token' => $value] as
    // form data — MakesHttpRequests::post() encodes the body as JSON.
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteClasse(): void
    {
        $classe = ClasseFactory::createOne(['nom' => 'À Supprimer'])->_real();
        $classeId = $classe->getId();

        $this->get($this->generateIndexUrl());
        $this->assertResponseIsSuccessful();
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/classe/' . $classeId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Classe::class, $classeId));
    }

    public function testDeleteReducesClasseCount(): void
    {
        ClasseFactory::createMany(3);
        $toDelete = ClasseFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/classe/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(3, $this->entityManager->getRepository(Classe::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDeleteClasse(): void
    {
        $classe = ClasseFactory::createOne()->_real();
        $classeId = $classe->getId();

        // EasyAdmin redirects even on bad CSRF token but does NOT delete the entity
        $this->client->request('POST', '/admin/classe/' . $classeId . '/delete', ['token' => 'invalid-token']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Classe::class, $classeId));
    }
}
