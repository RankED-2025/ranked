<?php

namespace App\Tests\Controller\Professor;

use App\Factory\ClasseFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProfessorClassTest extends WebTestCase
{
    use ResetDatabase;

    public function testListForbiddenForEleve(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'profclass.eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'profclass.eleve@example.com', 'password123');

        $client->request(
            'GET',
            '/api/professor/classes',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testListSuccessForProfessor(): void
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
