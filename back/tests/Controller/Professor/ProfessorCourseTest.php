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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProfessorCourseTest extends WebTestCase
{
    use ResetDatabase;

    public function testCreateForbiddenForEleve(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'profcourse.eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'profcourse.eleve@example.com', 'password123');

        $client->request(
            'POST',
            '/api/professor/courses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode(['matiere_id' => 1])
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCourseOrClassNotFoundOnAssign(): void
    {
        $client = self::createClient();

        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $validClass = ClasseFactory::createOne();
        $validCourse = CoursFactory::createOne();

        $token = $this->authenticateAndGetToken($client, 'profcourse.prof@example.com', 'password123');
        $client->request(
            'POST',
            '/api/professor/courses/assign',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'cours_id' => $validCourse->getId(),
                'classe_id' => 999,
            ])
        );
        $this->assertResponseStatusCodeSame(404);

        $client->request(
            'POST',
            '/api/professor/courses/assign',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'cours_id' => 999,
                'classe_id' => $validClass->getId(),
            ])
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateWithoutMatiereOrWrongOne(): void
    {
        $client = self::createClient();

        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $difficulte = DifficulteFactory::createOne();

        $token = $this->authenticateAndGetToken($client, 'profcourse.prof@example.com', 'password123');

        $client->request(
            'POST',
            '/api/professor/courses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'difficulte_id' => $difficulte->getId(),
            ])
        );

        $this->assertResponseStatusCodeSame(400);

        $client->request(
            'POST',
            '/api/professor/courses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'matiere_id' => 123,
                'difficulte_id' => $difficulte->getId(),
            ])
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateWithoutDifficulteOrWrongOne(): void
    {
        $client = self::createClient();

        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $matiere = MatiereFactory::createOne();

        $token = $this->authenticateAndGetToken($client, 'profcourse.prof@example.com', 'password123');

        $client->request(
            'POST',
            '/api/professor/courses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'matiere_id' => $matiere->getId(),
            ])
        );

        $this->assertResponseStatusCodeSame(400);

        $client->request(
            'POST',
            '/api/professor/courses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'matiere_id' => $matiere->getId(),
                'difficulte_id' => 123,
            ])
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateSuccessForProfessor(): void
    {
        $client = self::createClient();

        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $matiere = MatiereFactory::createOne();
        $difficulte = DifficulteFactory::createOne();

        $token = $this->authenticateAndGetToken($client, 'profcourse.prof@example.com', 'password123');

        $client->request(
            'POST',
            '/api/professor/courses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'matiere_id' => $matiere->getId(),
                'difficulte_id' => $difficulte->getId(),
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('matiere', $responseData);
    }

    public function testOnlyProfessorCanAssignCourseToClass(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'profcourse.eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'profcourse.eleve@example.com', 'password123');

        $client->request(
            'POST',
            '/api/professor/courses/assign',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAssignCourseToClassSuccess(): void
    {
        $client = self::createClient();

        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne();
        $cours = CoursFactory::createOne();

        $token = $this->authenticateAndGetToken($client, 'profcourse.prof@example.com', 'password123');

        $client->request(
            'POST',
            '/api/professor/courses/assign',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'cours_id' => $cours->getId(),
                'classe_id' => $classe->getId(),
            ])
        );

        $this->assertResponseStatusCodeSame(200);
    }

    public function testAssignCreatesProgressionsForClassStudentsWithoutDuplicate(): void
    {
        $client = self::createClient();

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

        $token = $this->authenticateAndGetToken($client, $prof->getEmail(), 'password123');

        $client->request(
            'POST',
            '/api/professor/courses/assign',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'cours_id' => $cours->getId(),
                'classe_id' => $classe->getId(),
            ])
        );

        $this->assertResponseStatusCodeSame(200);

        /** @var ManagerRegistry $doctrine */
        $doctrine = self::getContainer()->get(ManagerRegistry::class);
        $progressionRepository = $doctrine->getRepository(Progression::class);

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
        $client = self::createClient();

        ProfesseurFactory::createOne([
            'email' => 'profcourse.prof2@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'profcourse.prof2@example.com', 'password123');

        $client->request(
            'POST',
            '/api/professor/courses/assign',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetMyCoursesSuccessForProfessor(): void
    {
        $client = self::createClient();

        $prof = ProfesseurFactory::createOne([
            'email' => 'profcourse.prof3@example.com',
            'password' => 'password123',
        ]);

        CoursFactory::createOne(['professeur' => $prof]);

        $token = $this->authenticateAndGetToken($client, 'profcourse.prof3@example.com', 'password123');

        $client->request(
            'GET',
            '/api/professor/courses',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('id', $responseData[0]);
    }

    public function testGetMyCoursesForbiddenForEleve(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'profcourse.eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'profcourse.eleve@example.com', 'password123');

        $client->request(
            'GET',
            '/api/professor/courses',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAssignCourseToClassForbiddenForOtherProfessor(): void
    {
        $client = self::createClient();

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
        $token = $this->authenticateAndGetToken($client, $prof2->getEmail(), 'password123');
        $client->request(
            'POST',
            '/api/professor/courses/assign',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'cours_id' => $cours->getId(),
                'classe_id' => $classe->getId(),
            ])
        );
        $this->assertResponseStatusCodeSame(403);

        $token = $this->authenticateAndGetToken($client, $prof1->getEmail(), 'password123');
        $client->request(
            'POST',
            '/api/professor/courses/assign',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'cours_id' => $cours->getId(),
                'classe_id' => $classe->getId(),
            ])
        );
        $this->assertResponseStatusCodeSame(403);
    }

    private function authenticateAndGetToken($client, string $email, string $password): string
    {
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        return $responseData['token'];
    }
}
