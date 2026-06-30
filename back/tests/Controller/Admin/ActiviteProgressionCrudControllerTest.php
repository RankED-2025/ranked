<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ActiviteProgressionCrudController;
use App\Controller\Admin\DashboardController;
use App\Entity\ActiviteProgression;
use App\Factory\ActiviteFactory;
use App\Factory\ActiviteProgressionFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class ActiviteProgressionCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return ActiviteProgressionCrudController::class;
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

    private function submitActiviteProgressionForm(string $url, int $eleveId, int $activiteId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'ActiviteProgression[eleve]'     => (string) $eleveId,
            'ActiviteProgression[activite]'  => (string) $activiteId,
            'ActiviteProgression[score]'     => '7',
            'ActiviteProgression[total]'     => '10',
            'ActiviteProgression[earnedPts]' => '5',
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
        ActiviteProgressionFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('eleve');
        $this->assertIndexColumnExists('activite');
        $this->assertIndexColumnExists('completedAt');
        $this->assertIndexColumnExists('score');
        $this->assertIndexColumnExists('total');
        $this->assertIndexColumnExists('earnedPts');
    }

    public function testIndexCountMatchesTotal(): void
    {
        ActiviteProgressionFactory::createMany(3);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(3);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $ap = ActiviteProgressionFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists(Action::EDIT, $ap->getId());
        $this->assertIndexEntityActionExists(Action::DELETE, $ap->getId());
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
        $ap = ActiviteProgressionFactory::createOne()->_real();

        $this->get($this->generateDetailUrl($ap->getId()));

        $this->assertResponseIsSuccessful();
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
        $this->assertFormFieldExists('eleve');
        $this->assertFormFieldExists('activite');
        $this->assertFormFieldExists('score');
        $this->assertFormFieldExists('total');
        $this->assertFormFieldExists('earnedPts');
    }

    public function testAdminCanCreateActiviteProgression(): void
    {
        $this->client->followRedirects();
        $eleve = EleveFactory::createOne()->_real();
        $activite = ActiviteFactory::createOne()->_real();

        $this->submitActiviteProgressionForm($this->generateNewFormUrl(), $eleve->getId(), $activite->getId());

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $result = $this->entityManager->getRepository(ActiviteProgression::class)
            ->findOneBy(['eleve' => $eleve->getId(), 'activite' => $activite->getId()]);
        $this->assertNotNull($result);
        $this->assertSame(7, $result->getScore());
        $this->assertSame(10, $result->getTotal());
        $this->assertSame(5, $result->getEarnedPts());
    }

    public function testCreatedActiviteProgressionAppearsInList(): void
    {
        $this->client->followRedirects();
        $eleve = EleveFactory::createOne()->_real();
        $activite = ActiviteFactory::createOne()->_real();

        $this->submitActiviteProgressionForm($this->generateNewFormUrl(), $eleve->getId(), $activite->getId());

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $ap = ActiviteProgressionFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($ap->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('eleve');
        $this->assertFormFieldExists('activite');
        $this->assertFormFieldExists('score');
        $this->assertFormFieldExists('total');
        $this->assertFormFieldExists('earnedPts');
    }

    public function testAdminCanEditActiviteProgression(): void
    {
        $this->client->followRedirects();
        $ap = ActiviteProgressionFactory::createOne()->_real();
        $newEleve = EleveFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($ap->getId()));
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'ActiviteProgression[eleve]'     => (string) $newEleve->getId(),
            'ActiviteProgression[activite]'  => (string) $ap->getActivite()->getId(),
            'ActiviteProgression[score]'     => '3',
            'ActiviteProgression[total]'     => '10',
            'ActiviteProgression[earnedPts]' => '2',
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(ActiviteProgression::class, $ap->getId());
        $this->assertSame($newEleve->getId(), $updated->getEleve()->getId());
        $this->assertSame(3, $updated->getScore());
        $this->assertSame(10, $updated->getTotal());
        $this->assertSame(2, $updated->getEarnedPts());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteActiviteProgression(): void
    {
        $ap = ActiviteProgressionFactory::createOne()->_real();
        $apId = $ap->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/activite-progression/' . $apId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(ActiviteProgression::class, $apId));
    }

    public function testDeleteReducesCount(): void
    {
        ActiviteProgressionFactory::createMany(2);
        $toDelete = ActiviteProgressionFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/activite-progression/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(2, $this->entityManager->getRepository(ActiviteProgression::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $ap = ActiviteProgressionFactory::createOne()->_real();
        $apId = $ap->getId();

        $this->client->request('POST', '/admin/activite-progression/' . $apId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(ActiviteProgression::class, $apId));
    }

    // -------------------------------------------------------------------------
    // Relations (AssociationField en index)
    // -------------------------------------------------------------------------

    public function testIndexEleveLinkPointsToCorrectDetailPage(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        ActiviteProgressionFactory::createOne(['eleve' => $eleve]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="eleve"] a')->attr('href');
        $this->assertStringEndsWith('/admin/eleve/' . $eleve->getId(), $href);
    }

    public function testIndexActiviteLinkPointsToCorrectDetailPage(): void
    {
        $activite = ActiviteFactory::createOne()->_real();
        ActiviteProgressionFactory::createOne(['activite' => $activite]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="activite"] a')->attr('href');
        $this->assertStringEndsWith('/admin/activite/' . $activite->getId(), $href);
    }

    // -------------------------------------------------------------------------
    // Detail — Relations
    // -------------------------------------------------------------------------

    public function testDetailPageShowsEleveLink(): void
    {
        $eleve = EleveFactory::createOne(['firstname' => 'Hugo', 'name' => 'Simon'])->_real();
        $ap = ActiviteProgressionFactory::createOne(['eleve' => $eleve])->_real();

        $this->get($this->generateDetailUrl($ap->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve/' . $eleve->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve/' . $eleve->getId() . '"]', 'Hugo Simon');
    }

    public function testDetailPageShowsActiviteLink(): void
    {
        $activite = ActiviteFactory::createOne()->_real();
        $ap = ActiviteProgressionFactory::createOne(['activite' => $activite])->_real();

        $this->get($this->generateDetailUrl($ap->getId()));

        $this->assertSelectorExists('a[href$="/admin/activite/' . $activite->getId() . '"]');
    }
}
