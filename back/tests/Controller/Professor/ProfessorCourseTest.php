<?php

namespace App\Tests\Controller\Professor;

use App\Factory\CoursFactory;
use App\Factory\DifficulteFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProfesseurFactory;
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
