<?php

namespace App\Tests\Controller\Courses\Progression;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Repository\ProgressionRepository;
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

        $this->put('/api/progression/'.$course->getId(), ['percentage' => 80], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertSame('Progression updated successfully', $responseData['message']);
    }

    public function testUpdateProgressionUpdatesThePercentage()
    {
        $user = EleveFactory::createOne([
            'email' => 'student.update@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        $oldProgress = ProgressionFactory::createOne([
            'eleve' => $user,
            'cours' => $course,
            'percentage' => 20,
        ]);

        $token = $this->authenticateAndGetToken('student.update@example.com', 'password123');

        $this->put('/api/progression/'.$course->getId(), ['percentage' => 80], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $repository = $this->getService(ProgressionRepository::class);
        $newProgress = $repository->find($oldProgress->getId());

        $this->assertSame(80, $newProgress->getPercentage());
    }

    public function testUpdateProgressionFailureWithMissingPercentage(): void
    {
        $user = EleveFactory::createOne([
            'email' => 'student.update2@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        $progress = ProgressionFactory::createOne([
            'eleve' => $user,
            'cours' => $course,
            'percentage' => 20,
        ]);

        $token = $this->authenticateAndGetToken('student.update2@example.com', 'password123');

        $this->put('/api/progression/'.$course->getId(), [], $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);

        $this->assertSame(
            20,
            $this->getService(ProgressionRepository::class)->find($progress->getId())->getPercentage()
        );
    }

    public function testUpdateProgressionWithoutAuthentication(): void
    {
        $user = EleveFactory::createOne([
            'email' => 'student.update2@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();

        $progress = ProgressionFactory::createOne([
            'eleve' => $user,
            'cours' => $course,
            'percentage' => 20,
        ]);

        $this->put('/api/progression/'.$course->getId(), ['percentage' => 80]);

        $this->assertResponseStatusCodeSame(401);

        // not updated
        $this->assertSame(
            20,
            $this->getService(ProgressionRepository::class)->find($progress->getId())->getPercentage()
        );
    }

    public function testUpdateProgressionAsProfessorForbidden(): void
    {
        // auth
        ProfesseurFactory::createOne([
            'email' => 'professor.update@example.com',
            'password' => 'password123',
        ]);

        $user = EleveFactory::createOne([
            'email' => 'student.update2@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();

        $progress = ProgressionFactory::createOne([
            'eleve' => $user,
            'cours' => $course,
            'percentage' => 20,
        ]);

        $token = $this->authenticateAndGetToken('professor.update@example.com', 'password123');

        $this->put('/api/progression/'.$course->getId(), ['percentage' => 80], $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);

        $this->assertSame(
            20,
            $this->getService(ProgressionRepository::class)->find($progress->getId())->getPercentage()
        );
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
