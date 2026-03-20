<?php

namespace App\Tests\Controller\Courses;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProgressionFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProgressionGetTest extends WebTestCase
{
    use ResetDatabase;

    /**
     * Test progression list retrieval with authenticated student
     */
    public function testGetProgressionListSuccess(): void
    {
        $client = self::createClient();

        $user = EleveFactory::createOne([
            'email' => 'student.get@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        ProgressionFactory::createOne([
            'eleve' => $user,
            'cours' => $course,
            'percentage' => 35,
        ]);

        $token = $this->authenticateAndGetToken($client, 'student.get@example.com', 'password123');

        $client->request(
            'GET',
            '/api/progression',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('cours', $responseData[0]);
        $this->assertArrayHasKey('pourcentage', $responseData[0]);
    }

    /**
     * Test progression list retrieval without authentication
     */
    public function testGetProgressionListWithoutAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/progression');

        $this->assertResponseStatusCodeSame(401);
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
        $this->assertArrayHasKey('token', $responseData);

        return $responseData['token'];
    }
}
