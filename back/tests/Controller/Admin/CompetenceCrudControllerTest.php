<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\CompetenceCrudController;
use App\Controller\Admin\DashboardController;
use App\Entity\Competence;
use App\Factory\CompetenceFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveCompetenceFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class CompetenceCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return CompetenceCrudController::class;
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

    private function submitCompetenceForm(string $url, string $nom, string $niveau, int $coursId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Competence[nom]'    => $nom,
            'Competence[niveau]' => $niveau,
            'Competence[cours]'  => (string) $coursId,
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
        CompetenceFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('nom');
        $this->assertIndexColumnExists('niveau');
        $this->assertIndexColumnExists('cours');
    }

    public function testIndexDisplaysExistingCompetence(): void
    {
        CompetenceFactory::createOne(['nom' => 'Savoir résoudre des équations']);

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Savoir résoudre des équations');
    }

    public function testIndexCountMatchesTotal(): void
    {
        CompetenceFactory::createMany(4);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(4);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $competence = CompetenceFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists('edit', $competence->getId());
        $this->assertIndexEntityActionExists('delete', $competence->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $competence = CompetenceFactory::createOne(['nom' => 'Maîtriser les fractions'])->_real();

        $this->get($this->generateDetailUrl($competence->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Maîtriser les fractions');
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
        $this->assertFormFieldExists('niveau');
        $this->assertFormFieldExists('cours');
    }

    public function testAdminCanCreateCompetence(): void
    {
        $this->client->followRedirects();
        $cours = CoursFactory::createOne()->_real();

        $this->submitCompetenceForm($this->generateNewFormUrl(), 'Nouvelle compétence', 'débutant', $cours->getId());

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $result = $this->entityManager->getRepository(Competence::class)->findOneBy(['nom' => 'Nouvelle compétence']);
        $this->assertNotNull($result);
        $this->assertSame('débutant', $result->getNiveau());
    }

    public function testCreatedCompetenceAppearsInList(): void
    {
        $this->client->followRedirects();
        $cours = CoursFactory::createOne()->_real();

        $this->submitCompetenceForm($this->generateNewFormUrl(), 'Compétence listée', 'avancé', $cours->getId());

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Compétence listée');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $competence = CompetenceFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($competence->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('nom');
        $this->assertFormFieldExists('niveau');
    }

    public function testEditFormPreFillsValues(): void
    {
        $competence = CompetenceFactory::createOne(['nom' => 'Géométrie', 'niveau' => 'intermédiaire'])->_real();

        $this->get($this->generateEditFormUrl($competence->getId()));

        $this->assertInputValueSame('Competence[nom]', 'Géométrie');
        $this->assertInputValueSame('Competence[niveau]', 'intermédiaire');
    }

    public function testEditPageShowsEmptyElevesCompetences(): void
    {
        $competence = CompetenceFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($competence->getId()));

        // eleveCompetencesView renders "Aucun élève" when no EleveCompetence exists
        $this->assertSelectorTextContains('body', 'Aucun élève');
    }

    public function testAdminCanEditCompetence(): void
    {
        $this->client->followRedirects();
        $competence = CompetenceFactory::createOne(['nom' => 'Ancien nom', 'niveau' => 'débutant'])->_real();

        $this->submitCompetenceForm(
            $this->generateEditFormUrl($competence->getId()),
            'Nouveau nom',
            'expert',
            $competence->getCours()->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Competence::class, $competence->getId());
        $this->assertSame('Nouveau nom', $updated->getNom());
        $this->assertSame('expert', $updated->getNiveau());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteCompetence(): void
    {
        $competence = CompetenceFactory::createOne()->_real();
        $competenceId = $competence->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/competence/' . $competenceId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Competence::class, $competenceId));
    }

    public function testDeleteReducesCount(): void
    {
        CompetenceFactory::createMany(3);
        $toDelete = CompetenceFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/competence/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(3, $this->entityManager->getRepository(Competence::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $competence = CompetenceFactory::createOne()->_real();
        $competenceId = $competence->getId();

        $this->client->request('POST', '/admin/competence/' . $competenceId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Competence::class, $competenceId));
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function testIndexCoursLinkPointsToCorrectDetailPage(): void
    {
        $cours = CoursFactory::createOne()->_real();
        CompetenceFactory::createOne(['cours' => $cours]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="cours"] a')->attr('href');
        $this->assertStringEndsWith('/admin/cours/' . $cours->getId(), $href);
    }

    public function testEditPageEleveCompetenceRowContainsEleveLink(): void
    {
        $competence = CompetenceFactory::createOne()->_real();
        $eleve = EleveFactory::createOne(['firstname' => 'Paul', 'name' => 'Dumont'])->_real();
        EleveCompetenceFactory::createOne(['competence' => $competence, 'eleve' => $eleve]);

        $this->get($this->generateEditFormUrl($competence->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve/' . $eleve->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve/' . $eleve->getId() . '"]', 'Paul Dumont');
    }

    public function testEditPageEleveCompetenceRowContainsVoirLink(): void
    {
        $competence = CompetenceFactory::createOne()->_real();
        $ec = EleveCompetenceFactory::createOne(['competence' => $competence])->_real();

        $this->get($this->generateEditFormUrl($competence->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve-competence/' . $ec->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve-competence/' . $ec->getId() . '"]', 'Voir');
    }
}
