<?php

namespace App\Tests\Controller\Courses\Progression;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\AuthenticatesUsers;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProgressionByCoursTest extends WebTestCase
{
    use ResetDatabase;
    use AuthenticatesUsers;

    public function testGetProgressionByCoursSuccess(): void
    {
        $client = self::createClient();

        $user = EleveFactory::createOne([
            'email' => 'student.bycours@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        ProgressionFactory::createOne([
            'eleve' => $user,
            'cours' => $course,
            'percentage' => 50,
        ]);

        $token = $this->authenticateAndGetToken($client, 'student.bycours@example.com', 'password123');

        $client->request(
            'GET',
            '/api/progression/'.$course->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('cours', $responseData);
        $this->assertArrayHasKey('pourcentage', $responseData);
        $this->assertSame(50, $responseData['pourcentage']);
    }

    public function testGetProgressionByCoursWithoutAuthentication(): void
    {
        $client = self::createClient();
        $course = CoursFactory::createOne();

        $client->request('GET', '/api/progression/'.$course->getId());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetProgressionByCoursAsProfessor(): void
    {
        $client = self::createClient();

        ProfesseurFactory::createOne([
            'email' => 'professor.bycours@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        $token = $this->authenticateAndGetToken($client, 'professor.bycours@example.com', 'password123');

        $client->request(
            'GET',
            '/api/progression/'.$course->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetProgressionByCoursWithNonExistentCourse(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'student.bycours2@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken($client, 'student.bycours2@example.com', 'password123');

        $client->request(
            'GET',
            '/api/progression/99999',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ]
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetProgressionByCoursWithNoProgressionForCourse(): void
    {
        $client = self::createClient();

        EleveFactory::createOne([
            'email' => 'student.bycours3@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        $token = $this->authenticateAndGetToken($client, 'student.bycours3@example.com', 'password123');

        $client->request(
            'GET',
            '/api/progression/'.$course->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ]
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
