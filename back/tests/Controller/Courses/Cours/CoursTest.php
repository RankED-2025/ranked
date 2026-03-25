<?php

namespace App\Tests\Controller\Courses;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class CoursTest extends WebTestCase
{
    use ResetDatabase;

    public function testListWithoutAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/cours');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListWithAuthentication(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'cours.list@example.com',
            'password' => 'password123',
        ]);

        CoursFactory::createOne();

        $token = $this->authenticateAndGetToken($client, 'cours.list@example.com', 'password123');

        $client->request(
            'GET',
            '/api/cours',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('title', $responseData[0]);
    }

    public function testDetailWithoutAuthentication(): void
    {
        $client = self::createClient();

        $cours = CoursFactory::createOne();

        $client->request('GET', '/api/cours/'.$cours->getId());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDetailWithAuthentication(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'cours.detail@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();

        $token = $this->authenticateAndGetToken($client, 'cours.detail@example.com', 'password123');

        $client->request(
            'GET',
            '/api/cours/'.$cours->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('professeur', $responseData);
        $this->assertArrayHasKey('activites', $responseData);
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
