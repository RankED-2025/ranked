<?php

namespace App\Tests\Controller\Courses\Progression;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\AuthenticatesUsers;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProgressionUpdateTest extends WebTestCase
{
    use ResetDatabase;
    use AuthenticatesUsers;

    /**
     * Test successful progression update for authenticated student
     */
    public function testUpdateProgressionSuccess(): void
    {
        $client = self::createClient();

        $user = EleveFactory::createOne([
            'email' => 'student.update@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        ProgressionFactory::createOne([
            'eleve' => $user,
            'cours' => $course,
            'percentage' => 20,
        ]);

        $token = $this->authenticateAndGetToken($client, 'student.update@example.com', 'password123');

        $client->request(
            'PUT',
            '/api/progression/'.$course->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'percentage' => 80,
            ])
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
    }

    /**
     * Test progression update failure with missing percentage
     */
    public function testUpdateProgressionFailureWithMissingPercentage(): void
    {
        $client = self::createClient();

        $user = EleveFactory::createOne([
            'email' => 'student.update2@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        ProgressionFactory::createOne([
            'eleve' => $user,
            'cours' => $course,
            'percentage' => 20,
        ]);

        $token = $this->authenticateAndGetToken($client, 'student.update2@example.com', 'password123');

        $client->request(
            'PUT',
            '/api/progression/'.$course->getId(),
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

    /**
     * Test progression update without authentication
     */
    public function testUpdateProgressionWithoutAuthentication(): void
    {
        $client = self::createClient();

        $course = CoursFactory::createOne();

        $client->request(
            'PUT',
            '/api/progression/'.$course->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'percentage' => 80,
            ])
        );

        $this->assertResponseStatusCodeSame(401);
    }

}
