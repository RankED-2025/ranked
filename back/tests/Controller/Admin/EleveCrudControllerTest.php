<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\EleveCrudController;
use App\Entity\Eleve;
use App\Factory\ActiviteFactory;
use App\Factory\ActiviteProgressionFactory;
use App\Factory\BadgeFactory;
use App\Factory\ClasseFactory;
use App\Factory\CompetenceFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveCompetenceFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

class EleveCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;
    use GetsContainerServices;

    protected function getControllerFqcn(): string
    {
        return EleveCrudController::class;
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

    private function submitEleveForm(string $url, string $firstname, string $name, string $email, int $classeId, string $password = ''): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $values = [
            'Eleve[firstname]' => $firstname,
            'Eleve[name]'      => $name,
            'Eleve[email]'     => $email,
            'Eleve[classe]'    => (string) $classeId,
        ];
        if ($password !== '') {
            $values['Eleve[pwd_primitive]'] = $password;
        }

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form($values);
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
        EleveFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('firstname');
        $this->assertIndexColumnExists('name');
        $this->assertIndexColumnExists('email');
        $this->assertIndexColumnExists('classe');
    }

    public function testIndexCountMatchesTotal(): void
    {
        EleveFactory::createMany(3);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(3);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $eleve = EleveFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists(Action::EDIT, $eleve->getId());
        $this->assertIndexEntityActionExists(Action::DELETE, $eleve->getId());
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
        $eleve = EleveFactory::createOne(['firstname' => 'Emma', 'name' => 'Fontaine'])->_real();

        $this->get($this->generateDetailUrl($eleve->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Emma');
        $this->assertSelectorTextContains('body', 'Fontaine');
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
        $this->assertFormFieldExists('firstname');
        $this->assertFormFieldExists('name');
        $this->assertFormFieldExists('email');
        $this->assertFormFieldExists('classe');
    }

    public function testCreateFormDoesNotShowDisplayFields(): void
    {
        $this->get($this->generateNewFormUrl());

        $this->assertSelectorNotExists('#Eleve_progressionsView');
        $this->assertSelectorNotExists('#Eleve_eleveCompetencesView');
        $this->assertSelectorNotExists('#Eleve_activiteProgressionsView');
    }

    public function testAdminCanCreateEleve(): void
    {
        $this->client->followRedirects();
        $classe = ClasseFactory::createOne()->_real();

        $this->submitEleveForm(
            $this->generateNewFormUrl(),
            'Lucas',
            'Bernard',
            'lucas.bernard@example.com',
            $classe->getId(),
            'MotDePasseSecurise1!'
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $eleve = $this->entityManager->getRepository(Eleve::class)->findOneBy(['email' => 'lucas.bernard@example.com']);
        $this->assertNotNull($eleve);
        $this->assertSame($classe->getId(), $eleve->getClasse()->getId());
    }

    public function testCreatedElevePasswordIsHashed(): void
    {
        $this->client->followRedirects();
        $classe = ClasseFactory::createOne()->_real();

        $this->submitEleveForm(
            $this->generateNewFormUrl(),
            'Manon',
            'Girard',
            'manon.girard@example.com',
            $classe->getId(),
            'PlainPassword99!'
        );

        $this->entityManager->clear();
        $eleve = $this->entityManager->getRepository(Eleve::class)->findOneBy(['email' => 'manon.girard@example.com']);
        $this->assertNotNull($eleve);
        $this->assertNotSame('PlainPassword99!', $eleve->getPassword());
    }

    public function testCreatedEleveAppearsInList(): void
    {
        $this->client->followRedirects();
        $classe = ClasseFactory::createOne()->_real();

        $this->submitEleveForm(
            $this->generateNewFormUrl(),
            'Nathan',
            'Rousseau',
            'nathan.rousseau@example.com',
            $classe->getId(),
            'AutreMotDePasse1!'
        );

        $this->get($this->generateIndexUrl());
        $this->assertSelectorTextContains('body', 'Nathan');
    }

    public function testCreatedElevePasswordMatches()
    {
        $this->client->followRedirects();
        $classe = ClasseFactory::createOne()->_real();

        $this->submitEleveForm(
            $this->generateNewFormUrl(),
            'Jean',
            'Dupont',
            'jean.dupont@example.com',
            $classe->getId(),
            'SuperMotDePasse4321$!'
        );

        $this->entityManager->clear();
        $eleve = $this->entityManager->getRepository(Eleve::class)->findOneBy(['email' => 'jean.dupont@example.com']);
        $this->assertNotNull($eleve);

        $hasherService = $this->getService(UserPasswordHasherInterface::class);
        $this->assertTrue($hasherService->isPasswordValid($eleve, 'SuperMotDePasse4321$!'));
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $eleve = EleveFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($eleve->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('firstname');
        $this->assertFormFieldExists('email');
        $this->assertFormFieldExists('classe');
    }

    public function testEditFormPreFillsFirstname(): void
    {
        $eleve = EleveFactory::createOne(['firstname' => 'Olympe'])->_real();

        $this->get($this->generateEditFormUrl($eleve->getId()));

        $this->assertInputValueSame('Eleve[firstname]', 'Olympe');
    }

    public function testEditPageShowsEmptyDisplayFields(): void
    {
        $eleve = EleveFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($eleve->getId()));

        $this->assertSelectorTextContains('body', 'Aucune progression');
        $this->assertSelectorTextContains('body', 'Aucune compétence');
        $this->assertSelectorTextContains('body', "Aucune progression d'activité");
    }

    public function testAdminCanEditEleveWithoutChangingPassword(): void
    {
        $this->client->followRedirects();
        $classe = ClasseFactory::createOne()->_real();
        $eleve = EleveFactory::createOne([
            'firstname' => 'Ancien',
            'email'     => 'old.eleve@example.com',
            'classe'    => $classe,
        ])->_real();
        $oldHash = $eleve->getPassword();

        $this->submitEleveForm(
            $this->generateEditFormUrl($eleve->getId()),
            'Nouveau',
            $eleve->getName(),
            'old.eleve@example.com',
            $classe->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Eleve::class, $eleve->getId());
        $this->assertSame('Nouveau', $updated->getFirstname());
        $this->assertSame($oldHash, $updated->getPassword());
    }

    public function testAdminCanEditElevePassword(): void
    {
        $this->client->followRedirects();
        $classe = ClasseFactory::createOne()->_real();
        $eleve = EleveFactory::createOne([
            'firstname' => 'Ancien',
            'email'     => 'old.eleve@example.com',
            'classe'    => $classe,
        ])->_real();
        $oldHash = $eleve->getPassword();

        $this->submitEleveForm(
            $this->generateEditFormUrl($eleve->getId()),
            'Nouveau',
            $eleve->getName(),
            'old.eleve@example.com',
            $classe->getId(),
            '1234wowSuperMotDePasse!$'
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();

        $updated = $this->entityManager->find(Eleve::class, $eleve->getId());
        $this->assertNotSame($oldHash, $updated->getPassword());

        $hasherService = $this->getService(UserPasswordHasherInterface::class);
        $this->assertTrue($hasherService->isPasswordValid($updated, '1234wowSuperMotDePasse!$'));
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteEleve(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        $eleveId = $eleve->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/eleve/' . $eleveId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Eleve::class, $eleveId));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        $eleveId = $eleve->getId();

        $this->client->request('POST', '/admin/eleve/' . $eleveId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Eleve::class, $eleveId));
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function testIndexClasseLinkPointsToCorrectDetailPage(): void
    {
        $classe = ClasseFactory::createOne()->_real();
        EleveFactory::createOne(['classe' => $classe]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="classe"] a')->attr('href');
        $this->assertStringEndsWith('/admin/classe/' . $classe->getId(), $href);
    }

    public function testEditPageProgressionsTableShowsCoursLink(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        $cours = CoursFactory::createOne()->_real();
        $badge = BadgeFactory::createOne()->_real();
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'badge' => $badge]);

        $this->get($this->generateEditFormUrl($eleve->getId()));

        $this->assertSelectorExists('a[href$="/admin/cours/' . $cours->getId() . '"]');
    }

    public function testEditPageCompetencesTableShowsVoirLink(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        $competence = CompetenceFactory::createOne()->_real();
        $eleveComp = EleveCompetenceFactory::createOne(['eleve' => $eleve, 'competence' => $competence])->_real();

        $this->get($this->generateEditFormUrl($eleve->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve-competence/' . $eleveComp->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve-competence/' . $eleveComp->getId() . '"]', 'Voir');
    }

    public function testEditPageActiviteProgressionsTableShowsActiviteLink(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        $activite = ActiviteFactory::createOne()->_real();
        ActiviteProgressionFactory::createOne(['eleve' => $eleve, 'activite' => $activite]);

        $this->get($this->generateEditFormUrl($eleve->getId()));

        $this->assertSelectorExists('a[href$="/admin/activite/' . $activite->getId() . '"]');
    }

    // -------------------------------------------------------------------------
    // Detail — Relations
    // -------------------------------------------------------------------------

    public function testDetailPageShowsClasseLink(): void
    {
        $classe = ClasseFactory::createOne()->_real();
        $eleve = EleveFactory::createOne(['classe' => $classe])->_real();

        $this->get($this->generateDetailUrl($eleve->getId()));

        $this->assertSelectorExists('a[href$="/admin/classe/' . $classe->getId() . '"]');
    }

    public function testDetailPageProgressionsShowsCoursLink(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        $cours = CoursFactory::createOne(['titre' => 'Chimie organique'])->_real();
        $badge = BadgeFactory::createOne()->_real();
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'badge' => $badge]);

        $this->get($this->generateDetailUrl($eleve->getId()));

        $this->assertSelectorExists('a[href$="/admin/cours/' . $cours->getId() . '"]');
    }

    public function testDetailPageEleveCompetencesShowsVoirLink(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        $competence = CompetenceFactory::createOne()->_real();
        $ec = EleveCompetenceFactory::createOne(['eleve' => $eleve, 'competence' => $competence])->_real();

        $this->get($this->generateDetailUrl($eleve->getId()));

        $this->assertSelectorExists('a[href$="/admin/eleve-competence/' . $ec->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/eleve-competence/' . $ec->getId() . '"]', 'Voir');
    }

    public function testDetailPageActiviteProgressionsShowsActiviteLink(): void
    {
        $eleve = EleveFactory::createOne()->_real();
        $activite = ActiviteFactory::createOne()->_real();
        ActiviteProgressionFactory::createOne(['eleve' => $eleve, 'activite' => $activite]);

        $this->get($this->generateDetailUrl($eleve->getId()));

        $this->assertSelectorExists('a[href$="/admin/activite/' . $activite->getId() . '"]');
    }
}
