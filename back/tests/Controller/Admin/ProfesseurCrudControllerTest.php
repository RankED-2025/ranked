<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\ProfesseurCrudController;
use App\Entity\Professeur;
use App\Factory\ClasseFactory;
use App\Factory\CoursFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProfesseurCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return ProfesseurCrudController::class;
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

    private function submitProfesseurForm(string $url, string $firstname, string $name, string $email, string $password = ''): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $values = [
            'Professeur[firstname]' => $firstname,
            'Professeur[name]'      => $name,
            'Professeur[email]'     => $email,
        ];
        if ($password !== '') {
            $values['Professeur[pwd_primitive]'] = $password;
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

    public function testIndexHasExpectedColumns(): void
    {
        ProfesseurFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('firstname');
        $this->assertIndexColumnExists('name');
        $this->assertIndexColumnExists('email');
    }

    public function testIndexCountMatchesTotal(): void
    {
        ProfesseurFactory::createMany(3);

        $this->get($this->generateIndexUrl());

        // +1 car le profAdmin du setUp est aussi en base
        $this->assertIndexFullEntityCount(4);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists('edit', $prof->getId());
        $this->assertIndexEntityActionExists('delete', $prof->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $prof = ProfesseurFactory::createOne(['firstname' => 'Alice', 'name' => 'Martin'])->_real();

        $this->get($this->generateDetailUrl($prof->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Alice');
        $this->assertSelectorTextContains('body', 'Martin');
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
    }

    public function testCreateFormDoesNotShowDisplayFields(): void
    {
        $this->get($this->generateNewFormUrl());

        $this->assertSelectorNotExists('#Professeur_classesView');
        $this->assertSelectorNotExists('#Professeur_coursView');
    }

    public function testAdminCanCreateProfesseur(): void
    {
        $this->client->followRedirects();

        $this->submitProfesseurForm(
            $this->generateNewFormUrl(),
            'Bernard',
            'Dupont',
            'bernard.dupont@example.com',
            'MotDePasseSecurise1!'
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $prof = $this->entityManager->getRepository(Professeur::class)->findOneBy(['email' => 'bernard.dupont@example.com']);
        $this->assertNotNull($prof);
        $this->assertSame('Bernard', $prof->getFirstname());
    }

    public function testCreatedProfesseurPasswordIsHashed(): void
    {
        $this->client->followRedirects();

        $this->submitProfesseurForm(
            $this->generateNewFormUrl(),
            'Claire',
            'Leroy',
            'claire.leroy@example.com',
            'PlainPassword99!'
        );

        $this->entityManager->clear();
        $prof = $this->entityManager->getRepository(Professeur::class)->findOneBy(['email' => 'claire.leroy@example.com']);
        $this->assertNotNull($prof);
        // Le mot de passe doit être hashé, jamais le texte brut
        $this->assertNotSame('PlainPassword99!', $prof->getPassword());
    }

    public function testCreatedProfesseurAppearsInList(): void
    {
        $this->client->followRedirects();

        $this->submitProfesseurForm(
            $this->generateNewFormUrl(),
            'David',
            'Petit',
            'david.petit@example.com',
            'AutreMotDePasse1!'
        );

        $this->get($this->generateIndexUrl());
        $this->assertSelectorTextContains('body', 'David');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($prof->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('firstname');
        $this->assertFormFieldExists('email');
    }

    public function testEditFormPreFillsFirstname(): void
    {
        $prof = ProfesseurFactory::createOne(['firstname' => 'Élodie'])->_real();

        $this->get($this->generateEditFormUrl($prof->getId()));

        $this->assertInputValueSame('Professeur[firstname]', 'Élodie');
    }

    public function testEditPageShowsEmptyDisplayFields(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($prof->getId()));

        $this->assertSelectorTextContains('body', 'Aucune classe');
        $this->assertSelectorTextContains('body', 'Aucun cours');
    }

    public function testAdminCanEditProfesseurWithoutChangingPassword(): void
    {
        $this->client->followRedirects();
        $prof = ProfesseurFactory::createOne(['firstname' => 'Ancien', 'email' => 'old@example.com'])->_real();
        $oldHash = $prof->getPassword();

        // Soumettre sans le champ password → ne change pas le hash
        $this->submitProfesseurForm(
            $this->generateEditFormUrl($prof->getId()),
            'Nouveau',
            $prof->getName(),
            'old@example.com'
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Professeur::class, $prof->getId());
        $this->assertSame('Nouveau', $updated->getFirstname());
        $this->assertSame($oldHash, $updated->getPassword());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteProfesseur(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();
        $profId = $prof->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/professeur/' . $profId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Professeur::class, $profId));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();
        $profId = $prof->getId();

        $this->client->request('POST', '/admin/professeur/' . $profId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Professeur::class, $profId));
    }

    // -------------------------------------------------------------------------
    // Relations (display custom fields)
    // -------------------------------------------------------------------------

    public function testEditPageClassesTableShowsVoirLink(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();
        $classe = ClasseFactory::createOne(['professeur' => $prof])->_real();

        $this->get($this->generateEditFormUrl($prof->getId()));

        $this->assertSelectorExists('a[href$="/admin/classe/' . $classe->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/classe/' . $classe->getId() . '"]', 'Voir');
    }

    public function testEditPageCoursTableShowsVoirLink(): void
    {
        $prof = ProfesseurFactory::createOne()->_real();
        $cours = CoursFactory::createOne(['professeur' => $prof])->_real();

        $this->get($this->generateEditFormUrl($prof->getId()));

        $this->assertSelectorExists('a[href$="/admin/cours/' . $cours->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/cours/' . $cours->getId() . '"]', 'Voir');
    }
}
