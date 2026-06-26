<?php

namespace App\Tests\Controller\Courses\Progression;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\MakesHttpRequests;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProgressionByCoursTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    public static function percentageCourseProvider(): array
    {
        return [
            '50' => [50, 50],
            'big number returns 100' => [166871, 100],
            'PHP_INT_MAX returns 100' => [PHP_INT_MAX, 100],
            'negative number returns 0' => [-84, 0],
        ];
    }

    #[DataProvider('percentageCourseProvider')]
    public function testGetProgressionByCoursSuccess(
        float|int $percentage,
        float|int $expected,
    ): void {
        $user = EleveFactory::createOne([
            'email' => 'student.bycours@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        ProgressionFactory::createOne([
            'eleve' => $user,
            'cours' => $course,
            'percentage' => $percentage,
        ]);

        $token = $this->authenticateAndGetToken('student.bycours@example.com', 'password123');

        $this->get('/api/progression/'.$course->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertSame($course->getId(), $responseData['cours']['id']);
        $this->assertSame($expected, $responseData['pourcentage']);
    }

    public function testGetProgressionByCoursWithoutAuthentication(): void
    {
        $course = CoursFactory::createOne();

        $this->get('/api/progression/'.$course->getId());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetProgressionByCoursAsProfessor(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'professor.bycours@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        $token = $this->authenticateAndGetToken('professor.bycours@example.com', 'password123');

        $this->get('/api/progression/'.$course->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetProgressionByCoursWithNonExistentCourse(): void
    {
        EleveFactory::createOne([
            'email' => 'student.bycours2@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('student.bycours2@example.com', 'password123');

        $this->get('/api/progression/99999', $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetProgressionByCoursWithNoProgressionForCourse(): void
    {
        EleveFactory::createOne([
            'email' => 'student.bycours3@example.com',
            'password' => 'password123',
        ]);

        $course = CoursFactory::createOne();
        $token = $this->authenticateAndGetToken('student.bycours3@example.com', 'password123');

        $this->get('/api/progression/'.$course->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }
}
