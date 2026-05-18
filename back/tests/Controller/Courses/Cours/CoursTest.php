<?php

namespace App\Tests\Controller\Courses\Cours;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class CoursTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    public function testListWithoutAuthentication(): void
    {
        $this->get('/api/cours');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListWithAuthentication(): void
    {
        EleveFactory::createOne([
            'email' => 'cours.list@example.com',
            'password' => 'password123',
        ]);

        CoursFactory::createOne();

        $token = $this->authenticateAndGetToken('cours.list@example.com', 'password123');

        $client = $this->get('/api/cours', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('title', $responseData[0]);
    }

    public function testDetailWithoutAuthentication(): void
    {
        $cours = CoursFactory::createOne();

        $this->get('/api/cours/'.$cours->getId());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDetailWithAuthentication(): void
    {
        EleveFactory::createOne([
            'email' => 'cours.detail@example.com',
            'password' => 'password123',
        ]);

        $cours = CoursFactory::createOne();

        $token = $this->authenticateAndGetToken('cours.detail@example.com', 'password123');

        $client = $this->get('/api/cours/'.$cours->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('professeur', $responseData);
        $this->assertArrayHasKey('activites', $responseData);
    }

    public function testTopCoursesRequiresAuthentication(): void
    {
        $this->get('/api/cours/top?top=5');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testTopCoursesReturnsEmptyArrayWhenNoCourses(): void
    {
        EleveFactory::createOne([
            'email' => 'top.empty@example.com',
            'password' => 'password123',
        ]);
        $token = $this->authenticateAndGetToken('top.empty@example.com', 'password123');

        $client = $this->get('/api/cours/top?top=5', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testTopCoursesReturnsCorrectStructure(): void
    {
        EleveFactory::createOne([
            'email' => 'top.data@example.com',
            'password' => 'password123',
        ]);
        CoursFactory::createMany(3);

        $token = $this->authenticateAndGetToken('top.data@example.com', 'password123');

        $client = $this->get('/api/cours/top?top=5', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('cours', $responseData[0]);
        $this->assertArrayHasKey('average', $responseData[0]);
        $this->assertArrayHasKey('id', $responseData[0]['cours']);
        $this->assertArrayHasKey('titre', $responseData[0]['cours']);
    }

    public function testTopCoursesRespectsTopLimit(): void
    {
        EleveFactory::createOne([
            'email' => 'top.limit@example.com',
            'password' => 'password123',
        ]);
        CoursFactory::createMany(10);

        $token = $this->authenticateAndGetToken('top.limit@example.com', 'password123');

        $client = $this->get('/api/cours/top?top=3', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(3, json_decode($client->getResponse()->getContent(), true));
    }

    public function testTopCoursesUsesDefaultTopParam(): void
    {
        EleveFactory::createOne([
            'email' => 'top.default@example.com',
            'password' => 'password123',
        ]);
        CoursFactory::createMany(10);
        $token = $this->authenticateAndGetToken('top.default@example.com', 'password123');

        $client = $this->get('/api/cours/top', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(5, json_decode($client->getResponse()->getContent(), true));
    }

    public function testTopCoursesRejectsNonPositiveTopParam(): void
    {
        EleveFactory::createOne([
            'email' => 'top.invalid@example.com',
            'password' => 'password123',
        ]);
        $token = $this->authenticateAndGetToken('top.invalid@example.com', 'password123');

        $this->get('/api/cours/top?top=0', $this->withToken($token));

        // MapQueryString uses validationFailedStatusCode = 404 by default in Symfony
        $this->assertResponseStatusCodeSame(404);
    }
}
