<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\QuestionCrudController;
use App\Entity\Question;
use App\Factory\ProfesseurFactory;
use App\Factory\QcmFactory;
use App\Factory\QuestionFactory;
use App\Factory\ReponseFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class QuestionCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return QuestionCrudController::class;
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

    private function submitQuestionForm(string $url, string $enonce, int $qcmId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Question[enonce]' => $enonce,
            'Question[qcm]'    => (string) $qcmId,
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
        QuestionFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('enonce');
        $this->assertIndexColumnExists('qcm');
    }

    public function testIndexCountMatchesTotal(): void
    {
        QuestionFactory::createMany(3);

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(3);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $question = QuestionFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists(Action::EDIT, $question->getId());
        $this->assertIndexEntityActionExists(Action::DELETE, $question->getId());
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
        $question = QuestionFactory::createOne()->_real();

        $this->get($this->generateDetailUrl($question->getId()));

        $this->assertResponseIsSuccessful();
    }

    public function testDetailPageShowsEnonce(): void
    {
        $question = QuestionFactory::createOne(['enonce' => 'Quelle est la vitesse de la lumière ?'])->_real();

        $this->get($this->generateDetailUrl($question->getId()));

        $this->assertSelectorTextContains('body', 'Quelle est la vitesse de la lumière ?');
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
        $this->assertFormFieldExists('enonce');
        $this->assertFormFieldExists('qcm');
    }

    public function testCreateFormDoesNotShowReponsesField(): void
    {
        $this->get($this->generateNewFormUrl());

        // reponsesView is hidden on create via hideWhenCreating()
        $this->assertSelectorNotExists('#Question_reponsesView');
    }

    public function testAdminCanCreateQuestion(): void
    {
        $this->client->followRedirects();
        $qcm = QcmFactory::createOne()->_real();

        $this->submitQuestionForm($this->generateNewFormUrl(), 'Quelle est la capitale de la France ?', $qcm->getId());

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $result = $this->entityManager->getRepository(Question::class)
            ->findOneBy(['enonce' => 'Quelle est la capitale de la France ?']);
        $this->assertNotNull($result);
    }

    public function testCreatedQuestionAppearsInList(): void
    {
        $this->client->followRedirects();
        $qcm = QcmFactory::createOne()->_real();

        $this->submitQuestionForm($this->generateNewFormUrl(), 'Combien font 2+2 ?', $qcm->getId());

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $question = QuestionFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($question->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('enonce');
        $this->assertFormFieldExists('qcm');
    }

    public function testEditPageShowsEmptyReponsesList(): void
    {
        $question = QuestionFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($question->getId()));

        // reponsesView renders "Aucune réponse" when no réponses exist
        $this->assertSelectorTextContains('body', 'Aucune réponse');
    }

    public function testAdminCanEditQuestion(): void
    {
        $this->client->followRedirects();
        $question = QuestionFactory::createOne()->_real();

        $this->submitQuestionForm(
            $this->generateEditFormUrl($question->getId()),
            'Énoncé modifié ?',
            $question->getQcm()->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Question::class, $question->getId());
        $this->assertSame('Énoncé modifié ?', $updated->getEnonce());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteQuestion(): void
    {
        $question = QuestionFactory::createOne()->_real();
        $questionId = $question->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/question/' . $questionId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Question::class, $questionId));
    }

    public function testDeleteReducesCount(): void
    {
        QuestionFactory::createMany(2);
        $toDelete = QuestionFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/question/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(2, $this->entityManager->getRepository(Question::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $question = QuestionFactory::createOne()->_real();
        $questionId = $question->getId();

        $this->client->request('POST', '/admin/question/' . $questionId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Question::class, $questionId));
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function testIndexQcmLinkPointsToCorrectDetailPage(): void
    {
        $qcm = QcmFactory::createOne()->_real();
        QuestionFactory::createOne(['qcm' => $qcm]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="qcm"] a')->attr('href');
        $this->assertStringEndsWith('/admin/qcm/' . $qcm->getId(), $href);
    }

    public function testEditPageReponsesTableShowsVoirLink(): void
    {
        $question = QuestionFactory::createOne()->_real();
        $reponse = ReponseFactory::createOne(['question' => $question])->_real();

        $this->get($this->generateEditFormUrl($question->getId()));

        $this->assertSelectorExists('a[href$="/admin/reponse/' . $reponse->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/reponse/' . $reponse->getId() . '"]', 'Voir');
    }

    public function testEditPageReponsesTableShowsCorrectBadge(): void
    {
        $question = QuestionFactory::createOne()->_real();
        ReponseFactory::createOne(['question' => $question, 'isCorrect' => true]);

        $this->get($this->generateEditFormUrl($question->getId()));

        $this->assertSelectorTextContains('body', 'Oui');
    }

    // -------------------------------------------------------------------------
    // Detail — Relations
    // -------------------------------------------------------------------------

    public function testDetailPageShowsQcmLink(): void
    {
        $qcm = QcmFactory::createOne(['gainPts' => 10])->_real();
        $question = QuestionFactory::createOne(['qcm' => $qcm])->_real();

        $this->get($this->generateDetailUrl($question->getId()));

        $this->assertSelectorExists('a[href$="/admin/qcm/' . $qcm->getId() . '"]');
    }

    public function testDetailPageReponsesShowsVoirLink(): void
    {
        $question = QuestionFactory::createOne()->_real();
        $reponse = ReponseFactory::createOne(['question' => $question])->_real();

        $this->get($this->generateDetailUrl($question->getId()));

        $this->assertSelectorExists('a[href$="/admin/reponse/' . $reponse->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/reponse/' . $reponse->getId() . '"]', 'Voir');
    }
}
