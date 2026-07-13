<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ActiviteCrudController;
use App\Controller\Admin\DashboardController;
use App\Entity\Activite;
use App\Factory\ActiviteFactory;
use App\Factory\ActiviteProgressionFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class ActiviteCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return ActiviteCrudController::class;
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

    private function submitActiviteForm(string $url, string $type, int $ordre, int $coursId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Activite[type]'  => $type,
            'Activite[ordre]' => (string) $ordre,
            'Activite[cours]' => (string) $coursId,
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
        ActiviteFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('type');
        $this->assertIndexColumnExists('ordre');
        $this->assertIndexColumnExists('cours');
    }

    public function testIndexCountMatchesTotal(): void
    {
        ActiviteFactory::createMany(3);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(3);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $activite = ActiviteFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists(Action::EDIT, $activite->getId());
        $this->assertIndexEntityActionExists(Action::DELETE, $activite->getId());
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
        $activite = ActiviteFactory::createOne(['type' => 'contenu', 'ordre' => 1])->_real();

        $this->get($this->generateDetailUrl($activite->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Contenu');
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
        $this->assertFormFieldExists('ordre');
        $this->assertFormFieldExists('cours');
    }

    public function testCreateFormDoesNotShowCustomDisplayFields(): void
    {
        $this->get($this->generateNewFormUrl());

        // Champs display masqués à la création
        $this->assertSelectorNotExists('#Activite_contenuLink');
        $this->assertSelectorNotExists('#Activite_qcmLink');
    }

    public function testAdminCanCreateActivite(): void
    {
        $this->client->followRedirects();
        $cours = CoursFactory::createOne()->_real();

        $this->submitActiviteForm($this->generateNewFormUrl(), 'contenu', 3, $cours->getId());

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $result = $this->entityManager->getRepository(Activite::class)
            ->findOneBy(['type' => 'contenu', 'ordre' => 3]);
        $this->assertNotNull($result);
        $this->assertSame($cours->getId(), $result->getCours()->getId());
    }

    public function testCreatedActiviteAppearsInList(): void
    {
        $this->client->followRedirects();
        $cours = CoursFactory::createOne()->_real();

        $this->submitActiviteForm($this->generateNewFormUrl(), 'qcm', 2, $cours->getId());

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
        // EasyAdmin affiche le label du ChoiceField, pas la valeur stockée
        $this->assertSelectorTextContains('body', 'QCM');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $activite = ActiviteFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($activite->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('type');
        $this->assertFormFieldExists('ordre');
    }

    public function testEditPageShowsEmptyContenuLink(): void
    {
        $activite = ActiviteFactory::createOne(['type' => 'contenu', 'contenu' => null])->_real();

        $this->get($this->generateEditFormUrl($activite->getId()));

        // contenuLink renders "Aucun contenu lié" when no contenu is linked
        $this->assertSelectorTextContains('body', 'Aucun contenu lié');
    }

    public function testEditPageShowsEmptyQcmLink(): void
    {
        $activite = ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])->_real();

        $this->get($this->generateEditFormUrl($activite->getId()));

        // qcmLink renders "Aucun QCM lié" when no qcm is linked
        $this->assertSelectorTextContains('body', 'Aucun QCM lié');
    }

    public function testEditPageShowsEmptyActiviteProgressions(): void
    {
        $activite = ActiviteFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($activite->getId()));

        $this->assertSelectorTextContains('body', 'Aucune progression');
    }

    public function testAdminCanEditActivite(): void
    {
        $this->client->followRedirects();
        $activite = ActiviteFactory::createOne(['type' => 'contenu', 'ordre' => 1])->_real();

        $this->submitActiviteForm(
            $this->generateEditFormUrl($activite->getId()),
            'qcm',
            5,
            $activite->getCours()->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Activite::class, $activite->getId());
        $this->assertSame('qcm', $updated->getType());
        $this->assertSame(5, $updated->getOrdre());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteActivite(): void
    {
        $activite = ActiviteFactory::createOne()->_real();
        $activiteId = $activite->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/activite/' . $activiteId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Activite::class, $activiteId));
    }

    public function testDeleteReducesCount(): void
    {
        ActiviteFactory::createMany(2);
        $toDelete = ActiviteFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/activite/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(2, $this->entityManager->getRepository(Activite::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $activite = ActiviteFactory::createOne()->_real();
        $activiteId = $activite->getId();

        $this->client->request('POST', '/admin/activite/' . $activiteId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Activite::class, $activiteId));
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function testIndexCoursLinkPointsToCorrectDetailPage(): void
    {
        $cours = CoursFactory::createOne()->_real();
        ActiviteFactory::createOne(['cours' => $cours]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="cours"] a')->attr('href');
        $this->assertStringEndsWith('/admin/cours/' . $cours->getId(), $href);
    }

    public function testEditPageActiviteProgressionRowContainsEleveLink(): void
    {
        $activite = ActiviteFactory::createOne()->_real();
        $eleve = EleveFactory::createOne(['firstname' => 'Sophie', 'name' => 'Renard'])->_real();
        ActiviteProgressionFactory::createOne(['activite' => $activite, 'eleve' => $eleve]);

        $this->get($this->generateEditFormUrl($activite->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve/' . $eleve->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve/' . $eleve->getId() . '"]', 'Sophie Renard');
    }

    // -------------------------------------------------------------------------
    // Detail — Relations
    // -------------------------------------------------------------------------

    public function testDetailPageShowsCoursLink(): void
    {
        $cours = CoursFactory::createOne(['titre' => 'Physique quantique'])->_real();
        $activite = ActiviteFactory::createOne(['cours' => $cours])->_real();

        $this->get($this->generateDetailUrl($activite->getId()));

        $this->assertSelectorExists('a[href$="/admin/cours/' . $cours->getId() . '"]');
    }

    public function testDetailPageActiviteProgressionsShowsEleveLink(): void
    {
        $activite = ActiviteFactory::createOne()->_real();
        $eleve = EleveFactory::createOne(['firstname' => 'Chloé', 'name' => 'Laurent'])->_real();
        ActiviteProgressionFactory::createOne(['activite' => $activite, 'eleve' => $eleve]);

        $this->get($this->generateDetailUrl($activite->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve/' . $eleve->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve/' . $eleve->getId() . '"]', 'Chloé Laurent');
    }
}
