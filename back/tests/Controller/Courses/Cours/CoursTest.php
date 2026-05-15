<?php

namespace App\Tests\Controller\Courses\Cours;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Tests\Traits\AuthenticatesUsers;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class CoursTest extends WebTestCase
{
    use ResetDatabase;
    use AuthenticatesUsers;

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

    public function testTopCoursesRequiresAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/cours/top?top=5');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testTopCoursesReturnsEmptyArrayWhenNoCourses(): void
    {
        $client = self::createClient();
        EleveFactory::createOne([
            'email' => 'top.empty@example.com',
            'password' => 'password123',
        ]);
        $token = $this->authenticateAndGetToken($client, 'top.empty@example.com', 'password123');

        $client->request('GET', '/api/cours/top?top=5', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testTopCoursesReturnsCorrectStructure(): void
    {
        $client = self::createClient();
        EleveFactory::createOne([
            'email' => 'top.data@example.com',
            'password' => 'password123',
        ]);
        CoursFactory::createMany(3);
        $token = $this->authenticateAndGetToken($client, 'top.data@example.com', 'password123');

        $client->request('GET', '/api/cours/top?top=5', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

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
        $client = self::createClient();
        EleveFactory::createOne([
            'email' => 'top.limit@example.com',
            'password' => 'password123',
        ]);
        CoursFactory::createMany(10);
        $token = $this->authenticateAndGetToken($client, 'top.limit@example.com', 'password123');

        $client->request('GET', '/api/cours/top?top=3', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(3, json_decode($client->getResponse()->getContent(), true));
    }

    public function testTopCoursesUsesDefaultTopParam(): void
    {
        $client = self::createClient();
        EleveFactory::createOne([
            'email' => 'top.default@example.com',
            'password' => 'password123',
        ]);
        CoursFactory::createMany(10);
        $token = $this->authenticateAndGetToken($client, 'top.default@example.com', 'password123');

        $client->request('GET', '/api/cours/top', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(5, json_decode($client->getResponse()->getContent(), true));
    }

    public function testTopCoursesRejectsNonPositiveTopParam(): void
    {
        $client = self::createClient();
        EleveFactory::createOne([
            'email' => 'top.invalid@example.com',
            'password' => 'password123',
        ]);
        $token = $this->authenticateAndGetToken($client, 'top.invalid@example.com', 'password123');

        $client->request('GET', '/api/cours/top?top=0', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        // MapQueryString uses validationFailedStatusCode = 404 by default in Symfony
        $this->assertResponseStatusCodeSame(404);
    }

}
