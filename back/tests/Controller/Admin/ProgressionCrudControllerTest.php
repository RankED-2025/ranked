<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\ProgressionCrudController;
use App\Entity\Progression;
use App\Factory\BadgeFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProgressionCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return ProgressionCrudController::class;
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

    private function submitProgressionForm(string $url, int $eleveId, int $coursId, int $badgeId, int $percentage): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Progression[eleve]'      => (string) $eleveId,
            'Progression[cours]'      => (string) $coursId,
            'Progression[badge]'      => (string) $badgeId,
            'Progression[percentage]' => (string) $percentage,
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
        ProgressionFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('eleve');
        $this->assertIndexColumnExists('cours');
        $this->assertIndexColumnExists('badge');
        $this->assertIndexColumnExists('percentage');
    }

    public function testIndexCountMatchesTotal(): void
    {
        ProgressionFactory::createMany(4);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(4);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $progression = ProgressionFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists('edit', $progression->getId());
        $this->assertIndexEntityActionExists('delete', $progression->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $progression = ProgressionFactory::createOne(['percentage' => 75])->_real();

        $this->get($this->generateDetailUrl($progression->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '75');
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
        $this->assertFormFieldExists('cours');
        $this->assertFormFieldExists('badge');
        $this->assertFormFieldExists('percentage');
    }

    public function testAdminCanCreateProgression(): void
    {
        $this->client->followRedirects();
        $eleve = EleveFactory::createOne()->_real();
        $cours = CoursFactory::createOne()->_real();
        $badge = BadgeFactory::createOne()->_real();

        $this->submitProgressionForm($this->generateNewFormUrl(), $eleve->getId(), $cours->getId(), $badge->getId(), 60);

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $result = $this->entityManager->getRepository(Progression::class)
            ->findOneBy(['eleve' => $eleve->getId(), 'cours' => $cours->getId()]);
        $this->assertNotNull($result);
        $this->assertSame(60, $result->getPercentage());
    }

    public function testCreatedProgressionAppearsInList(): void
    {
        $this->client->followRedirects();
        $eleve = EleveFactory::createOne()->_real();
        $cours = CoursFactory::createOne()->_real();
        $badge = BadgeFactory::createOne()->_real();

        $this->submitProgressionForm($this->generateNewFormUrl(), $eleve->getId(), $cours->getId(), $badge->getId(), 50);

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $progression = ProgressionFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($progression->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('percentage');
    }

    public function testEditFormPreFillsPercentage(): void
    {
        $progression = ProgressionFactory::createOne(['percentage' => 42])->_real();

        $this->get($this->generateEditFormUrl($progression->getId()));

        $this->assertInputValueSame('Progression[percentage]', '42');
    }

    public function testAdminCanEditPercentage(): void
    {
        $this->client->followRedirects();
        $progression = ProgressionFactory::createOne(['percentage' => 20])->_real();

        $this->submitProgressionForm(
            $this->generateEditFormUrl($progression->getId()),
            $progression->getEleve()->getId(),
            $progression->getCours()->getId(),
            $progression->getBadge()->getId(),
            95
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Progression::class, $progression->getId());
        $this->assertSame(95, $updated->getPercentage());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteProgression(): void
    {
        $progression = ProgressionFactory::createOne()->_real();
        $progressionId = $progression->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/progression/' . $progressionId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Progression::class, $progressionId));
    }

    public function testDeleteReducesCount(): void
    {
        ProgressionFactory::createMany(3);
        $toDelete = ProgressionFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/progression/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(3, $this->entityManager->getRepository(Progression::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $progression = ProgressionFactory::createOne()->_real();
        $progressionId = $progression->getId();

        $this->client->request('POST', '/admin/progression/' . $progressionId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Progression::class, $progressionId));
    }

    // -------------------------------------------------------------------------
    // Relations (AssociationField en index)
    // -------------------------------------------------------------------------

    public function testIndexEleveLinkPointsToCorrectDetailPage(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        ProgressionFactory::createOne(['eleve' => $eleve]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="eleve"] a')->attr('href');
        $this->assertStringEndsWith('/admin/eleve/' . $eleve->getId(), $href);
    }

    public function testIndexCoursLinkPointsToCorrectDetailPage(): void
    {
        $cours = CoursFactory::createOne(['titre' => 'Cours Test'])->_real();
        ProgressionFactory::createOne(['cours' => $cours]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="cours"] a')->attr('href');
        $this->assertStringEndsWith('/admin/cours/' . $cours->getId(), $href);
    }

    public function testIndexBadgeLinkPointsToCorrectDetailPage(): void
    {
        $badge = BadgeFactory::createOne()->_real();
        ProgressionFactory::createOne(['badge' => $badge]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="badge"] a')->attr('href');
        $this->assertStringEndsWith('/admin/badge/' . $badge->getId(), $href);
    }
}
