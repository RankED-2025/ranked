<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\QcmCrudController;
use App\Entity\Qcm;
use App\Factory\ActiviteFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\QcmFactory;
use App\Factory\QuestionFactory;
use App\Tests\Traits\ExtractsEasyAdminTokens;
use App\Tests\Traits\MakesHttpRequests;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class QcmCrudControllerTest extends AbstractCrudTestCase
{
    use ResetDatabase;
    use ExtractsEasyAdminTokens;
    use MakesHttpRequests;

    protected function getControllerFqcn(): string
    {
        return QcmCrudController::class;
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

    private function submitQcmForm(string $url, int $gainPts, int $activiteId): void
    {
        $this->get($url);
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Qcm[gainPts]'  => (string) $gainPts,
            'Qcm[activite]' => (string) $activiteId,
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
        QcmFactory::createOne();

        $this->get($this->generateIndexUrl());

        $this->assertResponseIsSuccessful();
        $this->assertIndexColumnExists('gainPts');
        $this->assertIndexColumnExists('activite');
    }

    public function testIndexCountMatchesTotal(): void
    {
        // createMany réutiliserait la même activite (contrainte UNIQUE sur qcm.activite_id)
        for ($i = 0; $i < 3; $i++) {
            QcmFactory::createOne(['activite' => ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])]);
        }

        $this->get($this->generateIndexUrl());

        $this->assertIndexFullEntityCount(3);
    }

    public function testIndexShowsEditAndDeleteActionsPerRow(): void
    {
        $qcm = QcmFactory::createOne()->_real();

        $this->get($this->generateIndexUrl());

        $this->assertIndexEntityActionExists('edit', $qcm->getId());
        $this->assertIndexEntityActionExists('delete', $qcm->getId());
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function testDetailPageIsAccessible(): void
    {
        $qcm = QcmFactory::createOne(['gainPts' => 20])->_real();

        $this->get($this->generateDetailUrl($qcm->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '20');
    }

    public function testDetailPageReturns404ForNonExistentId(): void
    {
        $this->get($this->generateDetailUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Create
    //
    // Le select activite est filtré par QueryBuilder : seules les activités de
    // type 'qcm' sans QCM déjà lié apparaissent dans la liste.
    // -------------------------------------------------------------------------

    public function testCreateFormIsAccessible(): void
    {
        $this->get($this->generateNewFormUrl());

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('gainPts');
        $this->assertFormFieldExists('activite');
    }

    public function testCreateFormDoesNotShowQuestionsField(): void
    {
        $this->get($this->generateNewFormUrl());

        // questionsView est masqué à la création via hideWhenCreating()
        $this->assertSelectorNotExists('#Qcm_questionsView');
    }

    public function testAdminCanCreateQcm(): void
    {
        $this->client->followRedirects();
        // Activité de type 'qcm' sans QCM lié → apparaît dans le select filtré
        $activite = ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])->_real();

        $this->submitQcmForm($this->generateNewFormUrl(), 15, $activite->getId());

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $result = $this->entityManager->getRepository(Qcm::class)->findOneBy(['gainPts' => 15]);
        $this->assertNotNull($result);
        $this->assertSame($activite->getId(), $result->getActivite()->getId());
    }

    public function testCreatedQcmAppearsInList(): void
    {
        $this->client->followRedirects();
        $activite = ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])->_real();

        $this->submitQcmForm($this->generateNewFormUrl(), 25, $activite->getId());

        $this->get($this->generateIndexUrl());
        $this->assertIndexFullEntityCount(1);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function testEditFormIsAccessible(): void
    {
        $qcm = QcmFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($qcm->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertFormFieldExists('gainPts');
        $this->assertFormFieldExists('activite');
    }

    public function testEditFormPreFillsGainPts(): void
    {
        $qcm = QcmFactory::createOne(['gainPts' => 30])->_real();

        $this->get($this->generateEditFormUrl($qcm->getId()));

        $this->assertInputValueSame('Qcm[gainPts]', '30');
    }

    public function testEditPageShowsEmptyQuestionsList(): void
    {
        $qcm = QcmFactory::createOne()->_real();

        $this->get($this->generateEditFormUrl($qcm->getId()));

        $this->assertSelectorTextContains('body', 'Aucune question');
    }

    public function testAdminCanEditQcm(): void
    {
        $this->client->followRedirects();
        $qcm = QcmFactory::createOne(['gainPts' => 10])->_real();
        // Le QueryBuilder exclut les activités déjà liées → créer une activité disponible
        $newActivite = ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])->_real();

        $this->get($this->generateEditFormUrl($qcm->getId()));
        $form = $this->client->getCrawler()->filter('form[method="post"]')->form([
            'Qcm[gainPts]'  => '50',
            'Qcm[activite]' => (string) $newActivite->getId(),
        ]);
        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->entityManager->clear();
        $updated = $this->entityManager->find(Qcm::class, $qcm->getId());
        $this->assertSame(50, $updated->getGainPts());
    }

    public function testEditFormReturns404ForNonExistentId(): void
    {
        $this->get($this->generateEditFormUrl(99999));

        $this->assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function testAdminCanDeleteQcm(): void
    {
        $qcm = QcmFactory::createOne()->_real();
        $qcmId = $qcm->getId();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/qcm/' . $qcmId . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Qcm::class, $qcmId));
    }

    public function testDeleteReducesCount(): void
    {
        for ($i = 0; $i < 2; $i++) {
            QcmFactory::createOne(['activite' => ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])]);
        }
        $toDelete = QcmFactory::createOne(['activite' => ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])])->_real();

        $this->get($this->generateIndexUrl());
        $token = $this->extractDeleteToken();

        $this->client->request('POST', '/admin/qcm/' . $toDelete->getId() . '/delete', ['token' => $token]);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertSame(2, $this->entityManager->getRepository(Qcm::class)->count([]));
    }

    public function testDeleteWithInvalidTokenDoesNotDelete(): void
    {
        $qcm = QcmFactory::createOne()->_real();
        $qcmId = $qcm->getId();

        $this->client->request('POST', '/admin/qcm/' . $qcmId . '/delete', ['token' => 'invalid']);
        $this->assertResponseRedirects();

        $this->entityManager->clear();
        $this->assertNotNull($this->entityManager->find(Qcm::class, $qcmId));
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function testIndexActiviteLinkPointsToCorrectDetailPage(): void
    {
        $activite = ActiviteFactory::createOne(['type' => 'qcm', 'qcm' => null])->_real();
        QcmFactory::createOne(['activite' => $activite]);

        $this->get($this->generateIndexUrl());

        $href = $this->client->getCrawler()->filter('td[data-column="activite"] a')->attr('href');
        $this->assertStringEndsWith('/admin/activite/' . $activite->getId(), $href);
    }

    public function testEditPageQuestionsTableShowsVoirLink(): void
    {
        $qcm = QcmFactory::createOne()->_real();
        $question = QuestionFactory::createOne(['qcm' => $qcm])->_real();

        $this->get($this->generateEditFormUrl($qcm->getId()));

        $this->assertSelectorExists('a[href$="/admin/question/' . $question->getId() . '"]');
        $this->assertSelectorTextContains('a[href$="/admin/question/' . $question->getId() . '"]', 'Voir');
    }
}
