<?php

namespace App\Tests\Controller\Courses\Cours;

use App\Factory\CoursFactory;
use App\Factory\DifficulteFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
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

        $cours = CoursFactory::createOne();

        $token = $this->authenticateAndGetToken('cours.list@example.com', 'password123');

        $this->get('/api/cours', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertIsArray($responseData);

        $this->assertCount(1, $responseData);
        $this->assertSame($cours->getId(), $responseData[0]["id"]);
        $this->assertSame($cours->getTitre(), $responseData[0]["title"]);
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

        $matiere = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $difficulte = DifficulteFactory::createOne(['label' => 'Facile']);
        $cours = CoursFactory::createOne([
            'titre' => 'Le PHP',
            'matiere' => $matiere,
            'difficulte' => $difficulte,
        ]);

        $token = $this->authenticateAndGetToken('cours.detail@example.com', 'password123');

        $this->get('/api/cours/'.$cours->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();
        $this->assertSame($cours->getId(), $responseData['id']);
        $this->assertSame($cours->getProfesseur()->getId(), $responseData['professeur']['id']);
        $this->assertSame($matiere->getId(), $responseData['matiere']['id']);
        $this->assertSame($difficulte->getId(), $responseData['difficulte']['id']);
        $this->assertSame([], $responseData['activites']);
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

        $this->get('/api/cours/top?top=5', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testTopCoursesReturnsCorrectStructure(): void
    {
        EleveFactory::createOne([
            'email' => 'top.data@example.com',
            'password' => 'password123',
        ]);
        $cours = CoursFactory::createOne(['titre' => 'Top Cours Test']);

        $token = $this->authenticateAndGetToken('top.data@example.com', 'password123');

        $this->get('/api/cours/top?top=5', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();

        $this->assertCount(1, $responseData);
        $this->assertSame($cours->getId(), $responseData[0]['cours']['id']);
        $this->assertSame('Top Cours Test', $responseData[0]['cours']['titre']);
        $this->assertSame(0, $responseData[0]['average']);
    }

    public function testTopCoursesRespectsTopLimit(): void
    {
        EleveFactory::createOne([
            'email' => 'top.limit@example.com',
            'password' => 'password123',
        ]);
        CoursFactory::createMany(10);

        $token = $this->authenticateAndGetToken('top.limit@example.com', 'password123');

        $this->get('/api/cours/top?top=3', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(3, $this->getRequestResponse());
    }

    public function testTopCoursesUsesDefaultTopParam(): void
    {
        EleveFactory::createOne([
            'email' => 'top.default@example.com',
            'password' => 'password123',
        ]);
        CoursFactory::createMany(10);
        $token = $this->authenticateAndGetToken('top.default@example.com', 'password123');

        $this->get('/api/cours/top', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(5, $this->getRequestResponse());
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
