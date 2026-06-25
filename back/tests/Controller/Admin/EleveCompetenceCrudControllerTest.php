<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\EleveCompetenceCrudController;
use App\Entity\EleveCompetence;
use App\Factory\CompetenceFactory;
use App\Factory\EleveCompetenceFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class EleveCompetenceCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return EleveCompetenceCrudController::class;
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

    private function submitEleveCompetenceForm(string $url, int $eleveId, int $competenceId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'EleveCompetence[eleve]'      => (string) $eleveId,
            'EleveCompetence[competence]' => (string) $competenceId,
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
        EleveCompetenceFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('eleve');
        $this->assertIndexColumnExists('competence');
    }

    public function testIndexCountMatchesTotal(): void
    {
        EleveCompetenceFactory::createMany(3);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(3);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $ec = EleveCompetenceFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists('edit', $ec->getId());
        $this->assertIndexEntityActionExists('delete', $ec->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $ec = EleveCompetenceFactory::createOne()->_real();

        $this->get($this->generateDetailUrl($ec->getId()));

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
        $this->assertFormFieldExists('competence');
    }

    public function testAdminCanCreateEleveCompetence(): void
    {
        $this->client->followRedirects();
        $eleve = EleveFactory::createOne()->_real();
        $competence = CompetenceFactory::createOne()->_real();

        $this->submitEleveCompetenceForm($this->generateNewFormUrl(), $eleve->getId(), $competence->getId());

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $result = $this->entityManager->getRepository(EleveCompetence::class)
            ->findOneBy(['eleve' => $eleve->getId(), 'competence' => $competence->getId()]);
        $this->assertNotNull($result);
    }

    public function testCreatedEleveCompetenceAppearsInList(): void
    {
        $this->client->followRedirects();
        $eleve = EleveFactory::createOne()->_real();
        $competence = CompetenceFactory::createOne()->_real();

        $this->submitEleveCompetenceForm($this->generateNewFormUrl(), $eleve->getId(), $competence->getId());

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $ec = EleveCompetenceFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($ec->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('eleve');
        $this->assertFormFieldExists('competence');
    }

    public function testAdminCanEditEleveCompetence(): void
    {
        $this->client->followRedirects();
        $ec = EleveCompetenceFactory::createOne()->_real();
        $newCompetence = CompetenceFactory::createOne()->_real();

        $this->submitEleveCompetenceForm(
            $this->generateEditFormUrl($ec->getId()),
            $ec->getEleve()->getId(),
            $newCompetence->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(EleveCompetence::class, $ec->getId());
        $this->assertNotNull($updated);
        $this->assertSame($newCompetence->getId(), $updated->getCompetence()->getId());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteEleveCompetence(): void
    {
        $ec = EleveCompetenceFactory::createOne()->_real();
        $ecId = $ec->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/eleve-competence/' . $ecId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(EleveCompetence::class, $ecId));
    }

    public function testDeleteReducesCount(): void
    {
        EleveCompetenceFactory::createMany(2);
        $toDelete = EleveCompetenceFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/eleve-competence/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(2, $this->entityManager->getRepository(EleveCompetence::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $ec = EleveCompetenceFactory::createOne()->_real();
        $ecId = $ec->getId();

        $this->client->request('POST', '/admin/eleve-competence/' . $ecId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(EleveCompetence::class, $ecId));
    }

    // -------------------------------------------------------------------------
    // Relations (AssociationField en index)
    // -------------------------------------------------------------------------

    public function testIndexEleveAppearsAsLink(): void
    {
        EleveCompetenceFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertSelectorExists('td[data-column="eleve"] a');
    }

    public function testIndexEleveLinkPointsToCorrectDetailPage(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        EleveCompetenceFactory::createOne(['eleve' => $eleve]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="eleve"] a')->attr('href');
        $this->assertStringEndsWith('/admin/eleve/' . $eleve->getId(), $href);
    }

    public function testIndexCompetenceAppearsAsLink(): void
    {
        EleveCompetenceFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertSelectorExists('td[data-column="competence"] a');
    }

    public function testIndexCompetenceLinkPointsToCorrectDetailPage(): void
    {
        $competence = CompetenceFactory::createOne()->_real();
        EleveCompetenceFactory::createOne(['competence' => $competence]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="competence"] a')->attr('href');
        $this->assertStringEndsWith('/admin/competence/' . $competence->getId(), $href);
    }
}
