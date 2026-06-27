<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\CoursCrudController;
use App\Controller\Admin\DashboardController;
use App\Entity\Cours;
use App\Factory\ActiviteFactory;
use App\Factory\BadgeFactory;
use App\Factory\CompetenceFactory;
use App\Factory\CoursFactory;
use App\Factory\DifficulteFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class CoursCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return CoursCrudController::class;
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

    private function submitCoursForm(string $url, string $titre, string $description, int $matiereId, int $difficulteId, int $professeurId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Cours[titre]'       => $titre,
            'Cours[description]' => $description,
            'Cours[matiere]'     => (string) $matiereId,
            'Cours[difficulte]'  => (string) $difficulteId,
            'Cours[professeur]'  => (string) $professeurId,
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
        CoursFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('titre');
        $this->assertIndexColumnExists('matiere');
        $this->assertIndexColumnExists('difficulte');
        $this->assertIndexColumnExists('professeur');
    }

    public function testIndexDisplaysExistingCours(): void
    {
        CoursFactory::createOne(['titre' => 'Introduction à l\'algèbre']);

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Introduction à l\'algèbre');
    }

    public function testIndexCountMatchesTotal(): void
    {
        CoursFactory::createMany(4);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(4);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $cours = CoursFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists(Action::EDIT, $cours->getId());
        $this->assertIndexEntityActionExists(Action::DELETE, $cours->getId());
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
        $cours = CoursFactory::createOne(['titre' => 'Géométrie euclidienne'])->_real();

        $this->get($this->generateDetailUrl($cours->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Géométrie euclidienne');
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
        $this->assertFormFieldExists('titre');
        $this->assertFormFieldExists('matiere');
        $this->assertFormFieldExists('difficulte');
        $this->assertFormFieldExists('professeur');
    }

    public function testCreateFormDoesNotShowDisplayFields(): void
    {
        $this->get($this->generateNewFormUrl());

        $this->assertSelectorNotExists('#Cours_activitesView');
        $this->assertSelectorNotExists('#Cours_competencesView');
        $this->assertSelectorNotExists('#Cours_progressionsView');
    }

    public function testAdminCanCreateCours(): void
    {
        $this->client->followRedirects();
        $matiere = MatiereFactory::createOne()->_real();
        $difficulte = DifficulteFactory::createOne()->_real();
        $professeur = ProfesseurFactory::createOne()->_real();

        $this->submitCoursForm(
            $this->generateNewFormUrl(),
            'Calcul intégral',
            'Introduction au calcul intégral',
            $matiere->getId(),
            $difficulte->getId(),
            $professeur->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $result = $this->entityManager->getRepository(Cours::class)->findOneBy(['titre' => 'Calcul intégral']);
        $this->assertNotNull($result);
        $this->assertSame($matiere->getId(), $result->getMatiere()->getId());
    }

    public function testCreatedCoursAppearsInList(): void
    {
        $this->client->followRedirects();
        $matiere = MatiereFactory::createOne()->_real();
        $difficulte = DifficulteFactory::createOne()->_real();
        $professeur = ProfesseurFactory::createOne()->_real();

        $this->submitCoursForm(
            $this->generateNewFormUrl(),
            'Statistiques descriptives',
            'Introduction aux statistiques',
            $matiere->getId(),
            $difficulte->getId(),
            $professeur->getId()
        );

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Statistiques descriptives');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $cours = CoursFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($cours->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('titre');
        $this->assertFormFieldExists('matiere');
        $this->assertFormFieldExists('difficulte');
        $this->assertFormFieldExists('professeur');
    }

    public function testEditFormPreFillsTitre(): void
    {
        $cours = CoursFactory::createOne(['titre' => 'Trigonométrie'])->_real();

        $this->get($this->generateEditFormUrl($cours->getId()));

        $this->assertInputValueSame('Cours[titre]', 'Trigonométrie');
    }

    public function testEditPageShowsEmptyDisplayFields(): void
    {
        $cours = CoursFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($cours->getId()));

        $this->assertSelectorTextContains('body', 'Aucune activité');
        $this->assertSelectorTextContains('body', 'Aucune compétence');
        $this->assertSelectorTextContains('body', 'Aucune progression');
    }

    public function testAdminCanEditCours(): void
    {
        $this->client->followRedirects();
        $cours = CoursFactory::createOne(['titre' => 'Ancien titre'])->_real();

        $this->submitCoursForm(
            $this->generateEditFormUrl($cours->getId()),
            'Nouveau titre',
            'Nouvelle description',
            $cours->getMatiere()->getId(),
            $cours->getDifficulte()->getId(),
            $cours->getProfesseur()->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Cours::class, $cours->getId());
        $this->assertSame('Nouveau titre', $updated->getTitre());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteCours(): void
    {
        $cours = CoursFactory::createOne()->_real();
        $coursId = $cours->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/cours/' . $coursId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Cours::class, $coursId));
    }

    public function testDeleteReducesCount(): void
    {
        CoursFactory::createMany(3);
        $toDelete = CoursFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/cours/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(3, $this->entityManager->getRepository(Cours::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $cours = CoursFactory::createOne()->_real();
        $coursId = $cours->getId();

        $this->client->request('POST', '/admin/cours/' . $coursId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Cours::class, $coursId));
    }

    // -------------------------------------------------------------------------
    // Relations (3 AssociationFields en index + custom display fields)
    // -------------------------------------------------------------------------

    public function testIndexMatiereLinkPointsToCorrectDetailPage(): void
    {
        $matiere = MatiereFactory::createOne()->_real();
        CoursFactory::createOne(['matiere' => $matiere]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="matiere"] a')->attr('href');
        $this->assertStringEndsWith('/admin/matiere/' . $matiere->getId(), $href);
    }

    public function testIndexDifficulteLinkPointsToCorrectDetailPage(): void
    {
        $difficulte = DifficulteFactory::createOne()->_real();
        CoursFactory::createOne(['difficulte' => $difficulte]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="difficulte"] a')->attr('href');
        $this->assertStringEndsWith('/admin/difficulte/' . $difficulte->getId(), $href);
    }

    public function testIndexProfesseurLinkPointsToCorrectDetailPage(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();
        CoursFactory::createOne(['professeur' => $prof]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="professeur"] a')->attr('href');
        $this->assertStringEndsWith('/admin/professeur/' . $prof->getId(), $href);
    }

    public function testEditPageActivitesTableShowsVoirLink(): void
    {
        $cours = CoursFactory::createOne()->_real();
        $activite = ActiviteFactory::createOne(['cours' => $cours])->_real();

        $this->get($this->generateEditFormUrl($cours->getId()));

        $this->assertSelectorExists('a[href$="/admin/activite/' . $activite->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/activite/' . $activite->getId() . '"]', 'Voir');
    }

    public function testEditPageCompetencesTableShowsVoirLink(): void
    {
        $cours = CoursFactory::createOne()->_real();
        $competence = CompetenceFactory::createOne(['cours' => $cours])->_real();

        $this->get($this->generateEditFormUrl($cours->getId()));

        $this->assertSelectorExists('a[href$="/admin/competence/' . $competence->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/competence/' . $competence->getId() . '"]', 'Voir');
    }

    public function testEditPageProgressionsTableContainsEleveAndBadgeLinks(): void
    {
        $cours = CoursFactory::createOne()->_real();
        $eleve = EleveFactory::createOne(['firstname' => 'Tom', 'name' => 'Leroux'])->_real();
        $badge = BadgeFactory::createOne()->_real();
        ProgressionFactory::createOne(['cours' => $cours, 'eleve' => $eleve, 'badge' => $badge]);

        $this->get($this->generateEditFormUrl($cours->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve/' . $eleve->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve/' . $eleve->getId() . '"]', 'Tom Leroux');
        $this->assertSelectorExists('a[href$="/admin/badge/' . $badge->getId() . '"]');
    }

    // -------------------------------------------------------------------------
    // Detail — Relations
    // -------------------------------------------------------------------------

    public function testDetailPageShowsMatiereLink(): void
    {
        $matiere = MatiereFactory::createOne()->_real();
        $cours = CoursFactory::createOne(['matiere' => $matiere])->_real();

        $this->get($this->generateDetailUrl($cours->getId()));

        $this->assertSelectorExists('a[href$="/admin/matiere/' . $matiere->getId() . '"]');
    }

    public function testDetailPageShowsDifficulteLink(): void
    {
        $difficulte = DifficulteFactory::createOne()->_real();
        $cours = CoursFactory::createOne(['difficulte' => $difficulte])->_real();

        $this->get($this->generateDetailUrl($cours->getId()));

        $this->assertSelectorExists('a[href$="/admin/difficulte/' . $difficulte->getId() . '"]');
    }

    public function testDetailPageShowsProfesseurLink(): void
    {
        $prof = ProfesseurFactory::createOne(['firstname' => 'René', 'name' => 'Dupuis'])->_real();
        $cours = CoursFactory::createOne(['professeur' => $prof])->_real();

        $this->get($this->generateDetailUrl($cours->getId()));

        $this->assertSelectorExists('a[href$="/admin/professeur/' . $prof->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/professeur/' . $prof->getId() . '"]', 'René Dupuis');
    }

    public function testDetailPageActivitesShowsVoirLink(): void
    {
        $cours = CoursFactory::createOne()->_real();
        $activite = ActiviteFactory::createOne(['cours' => $cours])->_real();

        $this->get($this->generateDetailUrl($cours->getId()));

        $this->assertSelectorExists('a[href$="/admin/activite/' . $activite->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/activite/' . $activite->getId() . '"]', 'Voir');
    }

    public function testDetailPageProgressionsShowsEleveLink(): void
    {
        $cours = CoursFactory::createOne()->_real();
        $eleve = EleveFactory::createOne(['firstname' => 'Eva', 'name' => 'Garnier'])->_real();
        $badge = BadgeFactory::createOne()->_real();
        ProgressionFactory::createOne(['cours' => $cours, 'eleve' => $eleve, 'badge' => $badge]);

        $this->get($this->generateDetailUrl($cours->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve/' . $eleve->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve/' . $eleve->getId() . '"]', 'Eva Garnier');
    }
}
