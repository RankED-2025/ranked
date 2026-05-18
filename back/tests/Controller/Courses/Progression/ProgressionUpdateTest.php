<?php

namespace App\Tests\Controller\Courses\Progression;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProgressionUpdateTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    public function testUpdateProgressionSuccess(): void
    {
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

        $token = $this->authenticateAndGetToken('student.update@example.com', 'password123');

        $client = $this->put('/api/progression/'.$course->getId(), ['percentage' => 80], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
    }

    public function testUpdateProgressionFailureWithMissingPercentage(): void
    {
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

        $token = $this->authenticateAndGetToken('student.update2@example.com', 'password123');

        $this->put('/api/progression/'.$course->getId(), [], $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateProgressionWithoutAuthentication(): void
    {
        $course = CoursFactory::createOne();

        $this->put('/api/progression/'.$course->getId(), ['percentage' => 80]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateProgressionAsProfessorForbidden(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'professor.update@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        $token = $this->authenticateAndGetToken('professor.update@example.com', 'password123');

        $this->put('/api/progression/'.$course->getId(), ['percentage' => 80], $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdateProgressionWithNonExistentCourse(): void
    {
        EleveFactory::createOne([
            'email' => 'student.update3@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('student.update3@example.com', 'password123');

        $this->put('/api/progression/99999', ['percentage' => 80], $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateProgressionWithNoProgressionForCourse(): void
    {
        EleveFactory::createOne([
            'email' => 'student.update4@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        $token = $this->authenticateAndGetToken('student.update4@example.com', 'password123');

        $this->put('/api/progression/'.$course->getId(), ['percentage' => 80], $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }
}
