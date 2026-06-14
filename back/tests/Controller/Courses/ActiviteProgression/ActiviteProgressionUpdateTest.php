<?php

namespace App\Tests\Controller\Courses\ActiviteProgression;

use App\Factory\ActiviteFactory;
use App\Factory\ActiviteProgressionFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Repository\ActiviteProgressionRepository;
use App\Repository\ProgressionRepository;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ActiviteProgressionUpdateTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers, GetsContainerServices;

    public function testUpdateWithoutAuthentication(): void
    {
        $activite = ActiviteFactory::createOne();

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => true]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateAsProfessorForbidden(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'professor.activite-progression@example.com',
            'password' => 'password123',
        ]);

        $activite = ActiviteFactory::createOne();

        $token = $this->authenticateAndGetToken('professor.activite-progression@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => true], $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdateActivityNotFound(): void
    {
        EleveFactory::createOne([
            'email' => 'student.activite-progression-404@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('student.activite-progression-404@example.com', 'password123');

        $this->put('/api/activite-progression/999999', ['completed' => true], $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateWithMissingCompletedField(): void
    {
        EleveFactory::createOne([
            'email' => 'student.activite-progression-missing@example.com',
            'password' => 'password123',
        ]);

        $activite = ActiviteFactory::createOne();

        $token = $this->authenticateAndGetToken('student.activite-progression-missing@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), [], $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateWithNonBooleanCompletedField(): void
    {
        EleveFactory::createOne([
            'email' => 'student.activite-progression-nonbool@example.com',
            'password' => 'password123',
        ]);

        $activite = ActiviteFactory::createOne();

        $token = $this->authenticateAndGetToken('student.activite-progression-nonbool@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => 'yes'], $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateCompletedTrueCreatesActiviteProgression(): void
    {
        $eleve = EleveFactory::createOne([
            'email' => 'student.activite-progression-create@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours]);

        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);

        $token = $this->authenticateAndGetToken('student.activite-progression-create@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => true], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertSame('Activity progression updated successfully', $responseData['message']);

        $repository = $this->getService(ActiviteProgressionRepository::class);
        $activiteProgression = $repository->findOneBy([
            'eleve' => $eleve->_real(),
            'activite' => $activite->_real(),
        ]);

        $this->assertNotNull($activiteProgression);
        $this->assertNotNull($activiteProgression->getCompletedAt());
        $this->assertSame($eleve->getId(), $activiteProgression->getEleve()->getId());
        $this->assertSame($activite->getId(), $activiteProgression->getActivite()->getId());

        $queryResult = $repository
            ->createQueryBuilder('a')
            ->getQuery()
            ->getArrayResult();

        $this->assertCount(1, $queryResult);
    }

    public function testUpdateCompletedTrueOnAlreadyCompletedActiviteRefreshesCompletedAt(): void
    {
        $eleve = EleveFactory::createOne([
            'email' => 'student.activite-progression-refresh@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours]);

        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);

        $oldDate = new \DateTimeImmutable('-1 month');
        ActiviteProgressionFactory::createOne([
            'eleve' => $eleve,
            'activite' => $activite,
            'completedAt' => $oldDate,
        ]);

        $token = $this->authenticateAndGetToken('student.activite-progression-refresh@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => true], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $repository = $this->getService(ActiviteProgressionRepository::class);
        $activiteProgression = $repository->findOneBy([
            'eleve' => $eleve->_real(),
            'activite' => $activite->_real(),
        ]);

        $this->assertNotNull($activiteProgression->getCompletedAt());
        $this->assertGreaterThan($oldDate, $activiteProgression->getCompletedAt());

        $queryResult = $repository
            ->createQueryBuilder('a')
            ->getQuery()
            ->getArrayResult();

        $this->assertCount(1, $queryResult);
    }

    public function testUpdateCompletedFalseRemovesExistingActiviteProgression(): void
    {
        $eleve = EleveFactory::createOne([
            'email' => 'student.activite-progression-remove@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours]);

        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);

        ActiviteProgressionFactory::createOne([
            'eleve' => $eleve,
            'activite' => $activite,
            'completedAt' => new \DateTimeImmutable(),
        ]);

        $token = $this->authenticateAndGetToken('student.activite-progression-remove@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => false], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $repository = $this->getService(ActiviteProgressionRepository::class);
        $activiteProgression = $repository->findOneBy([
            'eleve' => $eleve->_real(),
            'activite' => $activite->_real(),
        ]);

        $this->assertNull($activiteProgression);

        $queryResult = $repository
            ->createQueryBuilder('a')
            ->getQuery()
            ->getArrayResult();

        $this->assertCount(0, $queryResult);
    }

    public function testUpdateCompletedFalseWithNoExistingProgressionDoesNothing(): void
    {
        EleveFactory::createOne([
            'email' => 'student.activite-progression-noop@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours]);

        $token = $this->authenticateAndGetToken('student.activite-progression-noop@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => false], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
    }

    public function testUpdateCompletedFalseWithNoExistingProgressionDoesNotAddAnyEntryInProgression(): void
    {
        EleveFactory::createOne([
            'email' => 'student.activite-progression-noop@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours]);

        $token = $this->authenticateAndGetToken('student.activite-progression-noop@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => false], $this->withToken($token));

        $repository = $this->getService(ActiviteProgressionRepository::class);

        $queryResult = $repository
            ->createQueryBuilder("a")
            ->getQuery()
            ->getArrayResult();

        $this->assertCount(0, $queryResult);
    }

    public function testUpdateRecalculatesCourseProgressionPercentage(): void
    {
        $eleve = EleveFactory::createOne([
            'email' => 'student.activite-progression-percentage@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();
        $activites = ActiviteFactory::createMany(4, ['cours' => $cours]);

        ProgressionFactory::createOne([
            'eleve' => $eleve,
            'cours' => $cours,
            'percentage' => 0,
        ]);

        $token = $this->authenticateAndGetToken('student.activite-progression-percentage@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activites[0]->getId(), ['completed' => true], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $progression = $this->getService(ProgressionRepository::class)->findOneBy([
            'eleve' => $eleve->_real(),
            'cours' => $cours->_real(),
        ]);

        $this->assertSame(25, $progression->getPercentage());

        $activiteProgression = $this->getService(ActiviteProgressionRepository::class)->findOneBy([
            'eleve' => $eleve->_real(),
            'activite' => $activites[0]->_real(),
        ]);

        $this->assertNotNull($activiteProgression);
        $this->assertNotNull($activiteProgression->getCompletedAt());
    }

    public function testUpdateOnUnassignedCourseDoesNotCreateActiviteProgression(): void
    {
        $eleve = EleveFactory::createOne([
            'email' => 'student.activite-progression-unassigned@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours]);

        $token = $this->authenticateAndGetToken('student.activite-progression-unassigned@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => true], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $repository = $this->getService(ActiviteProgressionRepository::class);
        $activiteProgression = $repository->findOneBy([
            'eleve' => $eleve->_real(),
            'activite' => $activite->_real(),
        ]);

        $this->assertNull($activiteProgression);

        $queryResult = $repository
            ->createQueryBuilder('a')
            ->getQuery()
            ->getArrayResult();

        $this->assertCount(0, $queryResult);

        $progression = $this->getService(ProgressionRepository::class)->findOneBy([
            'eleve' => $eleve->_real(),
            'cours' => $cours->_real(),
        ]);

        $this->assertNull($progression);
    }

    public function testUpdateWithNoProgressionForCourseDoesNotFail(): void
    {
        $eleve = EleveFactory::createOne([
            'email' => 'student.activite-progression-no-progression@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours]);

        $token = $this->authenticateAndGetToken('student.activite-progression-no-progression@example.com', 'password123');

        $this->put('/api/activite-progression/'.$activite->getId(), ['completed' => true], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $activiteProgression = $this->getService(ActiviteProgressionRepository::class)->findOneBy([
            'eleve' => $eleve->_real(),
            'activite' => $activite->_real(),
        ]);

        $this->assertNull($activiteProgression);
    }
}
