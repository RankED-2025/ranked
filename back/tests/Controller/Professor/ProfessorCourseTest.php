<?php

namespace App\Tests\Controller\Professor;

use App\Entity\Progression;
use App\Factory\BadgeFactory;
use App\Factory\CoursFactory;
use App\Factory\DifficulteFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProgressionFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ClasseFactory;
use App\Repository\ProgressionRepository;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProfessorCourseTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers, GetsContainerServices;

    public function testCreateForbiddenForEleve(): void
    {
        EleveFactory::createOne([
            'email' => 'profcourse.eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('profcourse.eleve@example.com', 'password123');

        $this->post('/api/professor/courses', ['matiere_id' => 1], $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCourseOrClassNotFoundOnAssign(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $validClass = ClasseFactory::createOne();
        $validCourse = CoursFactory::createOne();

        $token = $this->authenticateAndGetToken('profcourse.prof@example.com', 'password123');

        $this->post('/api/professor/courses/assign', [
            'cours_id' => $validCourse->getId(),
            'classe_id' => 999,
        ], $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);

        $this->post('/api/professor/courses/assign', [
            'cours_id' => 999,
            'classe_id' => $validClass->getId(),
        ], $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateWithoutMatiereOrWrongOne(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $difficulte = DifficulteFactory::createOne();

        $token = $this->authenticateAndGetToken('profcourse.prof@example.com', 'password123');

        $this->post('/api/professor/courses', [
            'difficulte_id' => $difficulte->getId(),
        ], $this->withToken($token));
        $this->assertResponseStatusCodeSame(400);

        $this->post('/api/professor/courses', [
            'matiere_id' => 123,
            'difficulte_id' => $difficulte->getId(),
        ], $this->withToken($token));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateWithoutDifficulte()
    {
        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $matiere = MatiereFactory::createOne();

        $token = $this->authenticateAndGetToken('profcourse.prof@example.com', 'password123');

        $this->post('/api/professor/courses', [
            'matiere_id' => $matiere->getId(),
        ], $this->withToken($token));
        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateWithWrongDifficulte(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $matiere = MatiereFactory::createOne();

        $token = $this->authenticateAndGetToken('profcourse.prof@example.com', 'password123');

        $this->post('/api/professor/courses', [
            'matiere_id' => $matiere->getId(),
            'difficulte_id' => 123,
        ], $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateSuccessForProfessor(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $matiere = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $difficulte = DifficulteFactory::createOne(['label' => 'Facile']);

        $token = $this->authenticateAndGetToken('profcourse.prof@example.com', 'password123');

        $this->post('/api/professor/courses', [
            'matiere_id' => $matiere->getId(),
            'difficulte_id' => $difficulte->getId(),
        ], $this->withToken($token));

        $this->assertResponseStatusCodeSame(201);

        $responseData = $this->getRequestResponse();
        $this->assertIsInt($responseData['id']);
        $this->assertSame($matiere->getId(), $responseData['matiere']);
        $this->assertSame('Facile', $responseData['difficulte']['label']);
    }

    public function testOnlyProfessorCanAssignCourseToClass(): void
    {
        EleveFactory::createOne([
            'email' => 'profcourse.eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('profcourse.eleve@example.com', 'password123');

        $this->post('/api/professor/courses/assign', [], $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAssignCourseToClassSuccess(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne();
        $cours = CoursFactory::createOne();

        $token = $this->authenticateAndGetToken('profcourse.prof@example.com', 'password123');

        $this->post('/api/professor/courses/assign', [
            'cours_id' => $cours->getId(),
            'classe_id' => $classe->getId(),
        ], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
    }

    public function testAssignCreatesProgressionsForClassStudentsWithoutDuplicate(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'profcourse.prof4@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne([
            'professeur' => $prof,
        ]);

        $eleve1 = EleveFactory::createOne([
            'classe' => $classe,
        ]);
        $eleve2 = EleveFactory::createOne([
            'classe' => $classe,
        ]);

        $cours = CoursFactory::createOne([
            'professeur' => $prof,
        ]);

        BadgeFactory::createOne([
            'type' => 'bronze',
        ]);

        $existingProgression = ProgressionFactory::createOne([
            'eleve' => $eleve1,
            'cours' => $cours,
            'percentage' => 50,
        ]);

        $token = $this->authenticateAndGetToken($prof->getEmail(), 'password123');

        $this->post('/api/professor/courses/assign', [
            'cours_id' => $cours->getId(),
            'classe_id' => $classe->getId(),
        ], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $progressionRepository = $this->getService(ProgressionRepository::class);

        $progressionEleve1 = $progressionRepository->findOneBy([
            'eleve' => $eleve1->getId(),
            'cours' => $cours,
        ]);
        $progressionEleve2 = $progressionRepository->findOneBy([
            'eleve' => $eleve2->getId(),
            'cours' => $cours,
        ]);

        $this->assertNotNull($progressionEleve1);
        $this->assertNotNull($progressionEleve2);

        $this->assertSame($existingProgression->getId(), $progressionEleve1->getId());
        $this->assertSame(0, $progressionEleve2->getPercentage());
        $this->assertNotNull($progressionEleve2->getBadge());
        $this->assertSame('bronze', $progressionEleve2->getBadge()->getType());
    }

    public function testAssignRequiresClasseAndCours(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof2@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('profcourse.prof2@example.com', 'password123');

        $this->post('/api/professor/courses/assign', [], $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetMyCoursesSuccessForProfessor(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'profcourse.prof3@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne([
            'professeur' => $prof,
            'matiere' => MatiereFactory::createOne(['libelle' => 'Physique-Chimie']),
        ]);

        $token = $this->authenticateAndGetToken('profcourse.prof3@example.com', 'password123');

        $this->get('/api/professor/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame($cours->getId(), $responseData[0]['id']);
        $this->assertSame($cours->getMatiere()->getId(), $responseData[0]['matiere']['id']);
    }

    public function testGetMyCoursesForbiddenForEleve(): void
    {
        EleveFactory::createOne([
            'email' => 'profcourse.eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('profcourse.eleve@example.com', 'password123');

        $this->get('/api/professor/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAssignCourseToClassForbiddenForOtherProfessor(): void
    {
        $prof1 = ProfesseurFactory::createOne([
            'email' => 'profcourse.prof1@example.com',
            'password' => 'password123',
        ]);
        $prof2 = ProfesseurFactory::createOne([
            'email' => 'profcourse.prof2@example.com',
            'password' => 'password123',
        ]);
        $classe = ClasseFactory::createOne(['professeur' => $prof2]);
        $cours = CoursFactory::createOne(['professeur' => $prof1]);

        $token = $this->authenticateAndGetToken($prof2->getEmail(), 'password123');
        $this->post('/api/professor/courses/assign', [
            'cours_id' => $cours->getId(),
            'classe_id' => $classe->getId(),
        ], $this->withToken($token));
        $this->assertResponseStatusCodeSame(403);

        $token = $this->authenticateAndGetToken($prof1->getEmail(), 'password123');
        $this->post('/api/professor/courses/assign', [
            'cours_id' => $cours->getId(),
            'classe_id' => $classe->getId(),
        ], $this->withToken($token));
        $this->assertResponseStatusCodeSame(403);
    }
}
