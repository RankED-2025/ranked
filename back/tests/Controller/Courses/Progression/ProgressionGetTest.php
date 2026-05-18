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

class ProgressionGetTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    public function testGetProgressionListSuccess(): void
    {
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

        $token = $this->authenticateAndGetToken('student.get@example.com', 'password123');

        $client = $this->get('/api/progression', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('cours', $responseData[0]);
        $this->assertArrayHasKey('pourcentage', $responseData[0]);
    }

    public function testGetProgressionListWithoutAuthentication(): void
    {
        $this->get('/api/progression');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetProgressionListAsProfessor(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'professor.get@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('professor.get@example.com', 'password123');

        $this->get('/api/progression', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }
}
