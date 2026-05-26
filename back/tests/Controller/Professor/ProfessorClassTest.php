<?php

namespace App\Tests\Controller\Professor;

use App\Factory\ClasseFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Factory\CoursFactory;
use App\Factory\DifficulteFactory;
use App\Factory\MatiereFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProfessorClassTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    public function testClassListSuccessForProfessor(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'profclass.prof@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne([
            'professeur' => $prof,
            'nom' => '5eme A',
        ]);

        $token = $this->authenticateAndGetToken('profclass.prof@example.com', 'password123');

        $this->get('/api/professor/classes', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame($classe->getId(), $responseData[0]['id']);
        $this->assertSame('5eme A', $responseData[0]['nom']);
    }

    public function testClassDetailsSuccessForProfessor(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'profclass.prof@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne([
            'professeur' => $prof,
            'nom' => '5eme A',
        ]);

        $eleve = EleveFactory::createOne([
            'classe' => $classe,
        ]);

        $cours = CoursFactory::createOne([
            'professeur' => $prof,
            'matiere' => MatiereFactory::createOne(),
            'difficulte' => DifficulteFactory::createOne(),
            'titre' => 'Cours de test',
            'description' => 'Description du cours de test',
        ]);

        ProgressionFactory::createOne([
            'eleve' => $eleve,
            'cours' => $cours,
            'percentage' => 50,
        ]);

        $token = $this->authenticateAndGetToken('profclass.prof@example.com', 'password123');

        $this->get('/api/professor/classes/'.$classe->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $responseData = $this->getRequestResponse();

        $this->assertSame($classe->getId(), $responseData['id']);
        $this->assertSame('5eme A', $responseData['nom']);
        $this->assertCount(1, $responseData['students']);
        $this->assertSame($eleve->getId(), $responseData['students'][0]['id']);
    }

    public function testStudentsProgressClassNotFound(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'profclass.prof2@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('profclass.prof2@example.com', 'password123');

        $this->get('/api/professor/classes/999999', $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUserIsNotProfessorForClassList(): void
    {
        EleveFactory::createOne([
            'email' => 'eleve@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get('/api/professor/classes', $this->withToken($token));

        $this->assertStringContainsString('Only professors can access this resource', $this->getResponseContent());
    }

    public function testUserIsNotProfessorForClassDetails(): void
    {
        EleveFactory::createOne([
            'email' => 'eleve@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne([
            'nom' => '5eme C',
        ]);

        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get('/api/professor/classes/'.$classe->getId(), $this->withToken($token));

        $this->assertStringContainsString('Only professors can access this resource', $this->getResponseContent());
    }

    public function testProfessorIsNotAssignedToThisClass(): void
    {
        $profOwner = ProfesseurFactory::createOne([
            'email' => 'profOwnerclass.prof3@example.com',
            'password' => 'password123',
        ]);

        $randomProf = ProfesseurFactory::createOne([
            'email' => 'NOTprofOwnerclass.prof3@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne([
            'professeur' => $profOwner,
            'nom' => '5eme B',
        ]);

        $token = $this->authenticateAndGetToken($randomProf->getEmail(), 'password123');

        $this->get('/api/professor/classes/'.$classe->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('You are not the professor of this class', $this->getResponseContent());
    }
}
