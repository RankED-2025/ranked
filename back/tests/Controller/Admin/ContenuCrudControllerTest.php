<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ContenuCrudController;
use App\Controller\Admin\DashboardController;
use App\Entity\Contenu;
use App\Factory\ActiviteFactory;
use App\Factory\ContenuFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class ContenuCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return ContenuCrudController::class;
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

    private function submitContenuForm(string $url, string $type, string $url_contenu, int $activiteId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Contenu[type]'    => $type,
            'Contenu[url]'     => $url_contenu,
            'Contenu[activite]' => (string) $activiteId,
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
        ContenuFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('type');
        $this->assertIndexColumnExists('url');
        $this->assertIndexColumnExists('activite');
    }

    public function testIndexCountMatchesTotal(): void
    {
        // createMany réutiliserait la même activite (contrainte UNIQUE sur contenu.activite_id)
        for ($i = 0; $i < 3; $i++) {
            ContenuFactory::createOne(['activite' => ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])]);
        }

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(3);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $contenu = ContenuFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists(Action::EDIT, $contenu->getId());
        $this->assertIndexEntityActionExists(Action::DELETE, $contenu->getId());
    }

    public function testIndexShowsAddButton(): void
    {
        $this->get($this->generateIndexUrl());

        $this->assertGlobalActionExists(Action::NEW);
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $contenu = ContenuFactory::createOne(['type' => 'video'])->_real();

        $this->get($this->generateDetailUrl($contenu->getId()));

        $this->assertResponseIsSuccessful();
    }

    public function testDetailPageReturns404ForNonExistentId(): void
    {
        $this->get($this->generateDetailUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Create
    //
    // Le select activite est filtré par QueryBuilder : seules les activités de
    // type 'contenu' sans contenu déjà lié apparaissent dans la liste.
    // -------------------------------------------------------------------------

    public function testCreateFormIsAccessible(): void
    {
        $this->get($this->generateNewFormUrl());

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('type');
        $this->assertFormFieldExists('url');
        $this->assertFormFieldExists('activite');
    }

    public function testAdminCanCreateContenu(): void
    {
        $this->client->followRedirects();
        // Activité de type 'contenu' sans contenu lié → apparaît dans le select filtré
        $activite = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();

        $this->submitContenuForm($this->generateNewFormUrl(), 'video', 'https://example.com/video.mp4', $activite->getId());

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $result = $this->entityManager->getRepository(Contenu::class)->findOneBy(['type' => 'video']);
        $this->assertNotNull($result);
        $this->assertSame($activite->getId(), $result->getActivite()->getId());
    }

    public function testCreatedContenuAppearsInList(): void
    {
        $this->client->followRedirects();
        $activite = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();

        $this->submitContenuForm($this->generateNewFormUrl(), 'pdf', 'https://example.com/doc.pdf', $activite->getId());

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
    }

    public function testCreateFormSelectOnlyContainsUnassignedContenuActivites(): void
    {
        // Doit apparaître : type 'contenu' sans contenu lié
        $available = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();

        // Ne doit PAS apparaître : type 'contenu' déjà pris par un contenu existant
        $takenActivite = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();
        ContenuFactory::createOne(['activite' => $takenActivite]);

        // Ne doit PAS apparaître : type 'qcm' (mauvais type)
        $wrongType = ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])->_real();

        $this->get($this->generateNewFormUrl());

        $this->assertSelectorExists('select[name="Contenu[activite]"] option[value="' . $available->getId() . '"]');
        $this->assertSelectorNotExists('select[name="Contenu[activite]"] option[value="' . $takenActivite->getId() . '"]');
        $this->assertSelectorNotExists('select[name="Contenu[activite]"] option[value="' . $wrongType->getId() . '"]');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $contenu = ContenuFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($contenu->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('type');
        $this->assertFormFieldExists('url');
    }

    public function testEditFormSelectOnlyContainsUnassignedContenuActivites(): void
    {
        // Activité courante du contenu → filtrée hors du select (déjà liée)
        $currentActivite = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();
        $contenu = ContenuFactory::createOne(['activite' => $currentActivite])->_real();

        // Doit apparaître : autre activité libre de type 'contenu'
        $available = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();

        // Ne doit PAS apparaître : activité prise par un autre contenu
        $takenActivite = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();
        ContenuFactory::createOne(['activite' => $takenActivite]);

        // Ne doit PAS apparaître : type 'qcm'
        $wrongType = ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])->_real();

        $this->get($this->generateEditFormUrl($contenu->getId()));

        $this->assertSelectorExists('select[name="Contenu[activite]"] option[value="' . $available->getId() . '"]');
        $this->assertSelectorNotExists('select[name="Contenu[activite]"] option[value="' . $currentActivite->getId() . '"]');
        $this->assertSelectorNotExists('select[name="Contenu[activite]"] option[value="' . $takenActivite->getId() . '"]');
        $this->assertSelectorNotExists('select[name="Contenu[activite]"] option[value="' . $wrongType->getId() . '"]');
    }

    public function testAdminCanEditContenu(): void
    {
        $this->client->followRedirects();
        $contenu = ContenuFactory::createOne(['type' => 'video', 'url' => 'https://old.example.com'])->_real();
        // Le QueryBuilder exclut les activités déjà liées → créer une activité disponible
        $newActivite = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();

        $this->get($this->generateEditFormUrl($contenu->getId()));
        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Contenu[type]'     => 'article',
            'Contenu[url]'      => 'https://new.example.com/article',
            'Contenu[activite]' => (string) $newActivite->getId(),
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Contenu::class, $contenu->getId());
        $this->assertSame('article', $updated->getType());
        $this->assertSame('https://new.example.com/article', $updated->getUrl());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteContenu(): void
    {
        $contenu = ContenuFactory::createOne()->_real();
        $contenuId = $contenu->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/contenu/' . $contenuId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Contenu::class, $contenuId));
    }

    public function testDeleteReducesCount(): void
    {
        for ($i = 0; $i < 2; $i++) {
            ContenuFactory::createOne(['activite' => ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])]);
        }
        $toDelete = ContenuFactory::createOne(['activite' => ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])])->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/contenu/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(2, $this->entityManager->getRepository(Contenu::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $contenu = ContenuFactory::createOne()->_real();
        $contenuId = $contenu->getId();

        $this->client->request('POST', '/admin/contenu/' . $contenuId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Contenu::class, $contenuId));
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function testIndexActiviteLinkPointsToCorrectDetailPage(): void
    {
        $activite = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();
        ContenuFactory::createOne(['activite' => $activite]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="activite"] a')->attr('href');
        $this->assertStringEndsWith('/admin/activite/' . $activite->getId(), $href);
    }

    // -------------------------------------------------------------------------
    // Detail — Relations
    // -------------------------------------------------------------------------

    public function testDetailPageShowsActiviteLink(): void
    {
        $activite = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();
        $contenu = ContenuFactory::createOne(['activite' => $activite])->_real();

        $this->get($this->generateDetailUrl($contenu->getId()));

        $this->assertSelectorExists('a[href$="/admin/activite/' . $activite->getId() . '"]');
    }
}
