<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\ReponseCrudController;
use App\Entity\Reponse;
use App\Factory\ProfesseurFactory;
use App\Factory\QuestionFactory;
use App\Factory\ReponseFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class ReponseCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return ReponseCrudController::class;
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

    private function submitReponseForm(string $url, string $texte, bool $isCorrect, int $questionId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Reponse[texte]'     => $texte,
            'Reponse[question]'  => (string) $questionId,
        ]);
        if ($isCorrect) {
            $form['Reponse[isCorrect]']->tick();
        } else {
            $form['Reponse[isCorrect]']->untick();
        }
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
        ReponseFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('texte');
        $this->assertIndexColumnExists('isCorrect');
        $this->assertIndexColumnExists('question');
    }

    public function testIndexDisplaysExistingReponse(): void
    {
        ReponseFactory::createOne(['texte' => 'La réponse correcte']);

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'La réponse correcte');
    }

    public function testIndexCountMatchesTotal(): void
    {
        ReponseFactory::createMany(5);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(5);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $reponse = ReponseFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists('edit', $reponse->getId());
        $this->assertIndexEntityActionExists('delete', $reponse->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $reponse = ReponseFactory::createOne(['texte' => 'Détail de la réponse'])->_real();

        $this->get($this->generateDetailUrl($reponse->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Détail de la réponse');
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
        $this->assertFormFieldExists('texte');
        $this->assertFormFieldExists('question');
    }

    public function testAdminCanCreateReponse(): void
    {
        $this->client->followRedirects();
        $question = QuestionFactory::createOne()->_real();

        $this->submitReponseForm($this->generateNewFormUrl(), 'Nouvelle réponse', true, $question->getId());

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $reponse = $this->entityManager->getRepository(Reponse::class)
            ->findOneBy(['texte' => 'Nouvelle réponse']);
        $this->assertNotNull($reponse);
        $this->assertTrue($reponse->isCorrect());
    }

    public function testCreatedReponseAppearsInList(): void
    {
        $this->client->followRedirects();
        $question = QuestionFactory::createOne()->_real();

        $this->submitReponseForm($this->generateNewFormUrl(), 'Réponse en liste', false, $question->getId());

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
        $this->assertSelectorTextContains('body', 'Réponse en liste');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $reponse = ReponseFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($reponse->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('texte');
        $this->assertFormFieldExists('isCorrect');
    }

    public function testAdminCanEditReponse(): void
    {
        $this->client->followRedirects();
        $reponse = ReponseFactory::createOne(['texte' => 'Ancienne réponse', 'isCorrect' => false])->_real();

        $this->submitReponseForm(
            $this->generateEditFormUrl($reponse->getId()),
            'Réponse modifiée',
            true,
            $reponse->getQuestion()->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Reponse::class, $reponse->getId());
        $this->assertSame('Réponse modifiée', $updated->getTexte());
        $this->assertTrue($updated->isCorrect());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteReponse(): void
    {
        $reponse = ReponseFactory::createOne()->_real();
        $reponseId = $reponse->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/reponse/' . $reponseId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Reponse::class, $reponseId));
    }

    public function testDeleteReducesCount(): void
    {
        ReponseFactory::createMany(2);
        $toDelete = ReponseFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/reponse/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(2, $this->entityManager->getRepository(Reponse::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $reponse = ReponseFactory::createOne()->_real();
        $reponseId = $reponse->getId();

        $this->client->request('POST', '/admin/reponse/' . $reponseId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Reponse::class, $reponseId));
    }

    // -------------------------------------------------------------------------
    // Relations (AssociationField en index)
    // -------------------------------------------------------------------------

    public function testIndexQuestionAppearsAsLink(): void
    {
        ReponseFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertSelectorExists('td[data-column="question"] a');
    }

    public function testIndexQuestionLinkPointsToCorrectDetailPage(): void
    {
        $question = QuestionFactory::createOne()->_real();
        ReponseFactory::createOne(['question' => $question]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="question"] a')->attr('href');
        $this->assertStringEndsWith('/admin/question/' . $question->getId(), $href);
    }
}
