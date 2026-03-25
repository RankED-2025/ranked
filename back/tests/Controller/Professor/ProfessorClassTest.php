<?php

namespace App\Tests\Controller\Professor;

use App\Factory\ClasseFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Factory\CoursFactory;
use App\Factory\DifficulteFactory;
use App\Factory\MatiereFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProfessorClassTest extends WebTestCase
{
    use ResetDatabase;

    public function testClassListSuccessForProfessor(): void
    {
        $client = self::createClient();

        $prof = ProfesseurFactory::createOne([
            'email' => 'profclass.prof@example.com',
            'password' => 'password123',
        ]);

        ClasseFactory::createOne([
            'professeur' => $prof,
            'nom' => '5eme A',
        ]);

        $token = $this->authenticateAndGetToken($client, 'profclass.prof@example.com', 'password123');

        $client->request(
            'GET',
            '/api/professor/classes',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('nom', $responseData[0]);
    }

    public function testClassDetailsSuccessForProfessor(): void
    {
        $client = self::createClient();

        $prof = ProfesseurFactory::createOne([
            'email' => 'profclass.prof@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne([
            'professeur' => $prof,
            'nom' => '5eme A',
        ]);

        $eleve = EleveFactory::createOne([
            'classe' => $classe,
        ]);

        $cours = CoursFactory::createOne([
            'professeur' => $prof,
            'matiere' => MatiereFactory::createOne(),
            'difficulte' => DifficulteFactory::createOne(),
            'titre' => 'Cours de test',
            'description' => 'Description du cours de test',
        ]);

        ProgressionFactory::createOne([
            'eleve' => $eleve,
            'cours' => $cours,
            'percentage' => 50,
        ]);

        $token = $this->authenticateAndGetToken($client, 'profclass.prof@example.com', 'password123');

        $client->request(
            'GET',
            '/api/professor/classes/'.$classe->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('nom', $responseData);
        $this->assertArrayHasKey('students', $responseData);
    }

    public function testStudentsProgressClassNotFound(): void
    {
        $client = self::createClient();

        ProfesseurFactory::createOne([
            'email' => 'profclass.prof2@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'profclass.prof2@example.com', 'password123');

        $client->request(
            'GET',
            '/api/professor/classes/999999',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUserIsNotProfessorForClassList(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request(
            'GET',
            '/api/professor/classes',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertStringContainsString('Only professors can access this resource', $client->getResponse()->getContent());
    }

    public function testUserIsNotProfessorForClassDetails(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'eleve@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne([
            'nom' => '5eme C',
        ]);

        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request(
            'GET',
            '/api/professor/classes/' . $classe->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertStringContainsString('Only professors can access this resource', $client->getResponse()->getContent());
    }

    public function testProfessorIsNotAssignedToThisClass(): void
    {
        $client = self::createClient();

        $profOwner = ProfesseurFactory::createOne([
            'email' => 'profOwnerclass.prof3@example.com',
            'password' => 'password123',
        ]);

        $randomProf = ProfesseurFactory::createOne([
            'email' => 'NOTprofOwnerclass.prof3@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne([
            'professeur' => $profOwner,
            'nom' => '5eme B',
        ]);

        $token = $this->authenticateAndGetToken($client, $randomProf->getEmail(), 'password123');

        $client->request(
            'GET',
            '/api/professor/classes/' .$classe->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('You are not the professor of this class', $client->getResponse()->getContent());
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
