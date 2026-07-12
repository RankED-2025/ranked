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

    // ── GET /api/professor/classes/{id}/courses ────────────────────────────

    public function testGetClassCoursesReturnsAssignedCourses(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'classcourses1@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        $cours = CoursFactory::createOne([
            'professeur' => $prof,
            'matiere' => MatiereFactory::createOne(['libelle' => 'Physique']),
            'difficulte' => DifficulteFactory::createOne(['label' => 'Facile']),
            'titre' => 'Cours de Physique',
            'description' => 'Description Physique',
        ]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'classe' => $classe]);

        $token = $this->authenticateAndGetToken('classcourses1@example.com', 'password123');
        $this->get('/api/professor/classes/'.$classe->getId().'/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();
        $this->assertCount(1, $data);
        $this->assertSame($cours->getId(), $data[0]['id']);
        $this->assertSame('Cours de Physique', $data[0]['title']);
        $this->assertSame('Description Physique', $data[0]['description']);
        $this->assertSame('Physique', $data[0]['matiere']['libelle']);
        $this->assertSame('Facile', $data[0]['difficulte']['label']);
    }

    public function testGetClassCoursesReturnsEmptyWhenNoStudents(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'classcourses2@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne(['professeur' => $prof]);

        $token = $this->authenticateAndGetToken('classcourses2@example.com', 'password123');
        $this->get('/api/professor/classes/'.$classe->getId().'/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testGetClassCoursesReturnsEmptyWhenStudentsHaveNoProgressions(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'classcourses3@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        EleveFactory::createOne(['classe' => $classe]);

        $token = $this->authenticateAndGetToken('classcourses3@example.com', 'password123');
        $this->get('/api/professor/classes/'.$classe->getId().'/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testGetClassCoursesDeduplicatesCoursesAcrossStudents(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'classcourses4@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $eleve1 = EleveFactory::createOne(['classe' => $classe]);
        $eleve2 = EleveFactory::createOne(['classe' => $classe]);
        $cours = CoursFactory::createOne([
            'professeur' => $prof,
            'matiere' => MatiereFactory::createOne(),
            'difficulte' => DifficulteFactory::createOne(),
        ]);
        ProgressionFactory::createOne(['eleve' => $eleve1, 'cours' => $cours, 'classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve2, 'cours' => $cours, 'classe' => $classe]);

        $token = $this->authenticateAndGetToken('classcourses4@example.com', 'password123');
        $this->get('/api/professor/classes/'.$classe->getId().'/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();

        $this->assertCount(1, $data);

        $id = $cours->getId();
        $this->assertNotNull($id);

        $this->assertSame($id, $data[0]['id']);
    }

    public function testGetClassCoursesReturnsMultipleDistinctCourses(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'classcourses5@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        $cours1 = CoursFactory::createOne([
            'professeur' => $prof,
            'matiere' => MatiereFactory::createOne(),
            'difficulte' => DifficulteFactory::createOne(),
        ]);
        $cours2 = CoursFactory::createOne([
            'professeur' => $prof,
            'matiere' => MatiereFactory::createOne(),
            'difficulte' => DifficulteFactory::createOne(),
        ]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours1, 'classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours2, 'classe' => $classe]);

        $token = $this->authenticateAndGetToken('classcourses5@example.com', 'password123');
        $this->get('/api/professor/classes/'.$classe->getId().'/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $data = $this->getRequestResponse();
        $this->assertCount(2, $data);

        $idCours1 = $cours1->getId();
        $idCours2 = $cours2->getId();

        $this->assertNotNull($idCours1);
        $this->assertNotNull($idCours2);

        $this->assertSame($idCours1, $data[0]['id']);
        $this->assertSame($idCours2, $data[1]['id']);
    }

    public function testGetClassCoursesDoesNotLeakCoursesFromPreviousClassOfAMovedStudent(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'classcourses.moved@example.com',
            'password' => 'password123',
        ]);

        $oldClasse = ClasseFactory::createOne(['professeur' => $prof]);
        $newClasse = ClasseFactory::createOne(['professeur' => $prof]);

        // the student used to be in $oldClasse and was assigned a course there,
        // then moved to $newClasse: that old progression must not leak into $newClasse.
        $eleve = EleveFactory::createOne(['classe' => $newClasse]);

        $oldCours = CoursFactory::createOne([
            'professeur' => $prof,
            'matiere' => MatiereFactory::createOne(),
            'difficulte' => DifficulteFactory::createOne(),
        ]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $oldCours, 'classe' => $oldClasse]);

        $token = $this->authenticateAndGetToken('classcourses.moved@example.com', 'password123');
        $this->get('/api/professor/classes/'.$newClasse->getId().'/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testGetClassCoursesExcludesCoursesOwnedByAnotherProfessor(): void
    {
        $prof = ProfesseurFactory::createOne([
            'email' => 'classcourses.otherprofcours@example.com',
            'password' => 'password123',
        ]);
        $otherProf = ProfesseurFactory::createOne([
            'email' => 'classcourses.otherprofcours2@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);

        $ownCours = CoursFactory::createOne([
            'professeur' => $prof,
            'matiere' => MatiereFactory::createOne(),
            'difficulte' => DifficulteFactory::createOne(),
        ]);
        $otherCours = CoursFactory::createOne([
            'professeur' => $otherProf,
            'matiere' => MatiereFactory::createOne(),
            'difficulte' => DifficulteFactory::createOne(),
        ]);

        // a progression can end up pointing to a course owned by another professor
        // (e.g. stale/inconsistent data) even though it is scoped to this classe
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $ownCours, 'classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $otherCours, 'classe' => $classe]);

        $token = $this->authenticateAndGetToken('classcourses.otherprofcours@example.com', 'password123');
        $this->get('/api/professor/classes/'.$classe->getId().'/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();

        $this->assertCount(1, $data);
        $this->assertSame($ownCours->getId(), $data[0]['id']);
    }

    public function testGetClassCoursesReturns404WhenClassNotFound(): void
    {
        ProfesseurFactory::createOne([
            'email' => 'classcourses6@example.com',
            'password' => 'password123',
        ]);

        $token = $this->authenticateAndGetToken('classcourses6@example.com', 'password123');
        $this->get('/api/professor/classes/999999/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetClassCoursesReturns403ForEleve(): void
    {
        EleveFactory::createOne([
            'email' => 'classcourses.eleve@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne();

        $token = $this->authenticateAndGetToken('classcourses.eleve@example.com', 'password123');
        $this->get('/api/professor/classes/'.$classe->getId().'/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('Only professors can access this resource', $this->getResponseContent());
    }

    public function testGetClassCoursesReturns403WhenProfessorDoesNotOwnClass(): void
    {
        $owner = ProfesseurFactory::createOne([
            'email' => 'classcourses.owner@example.com',
            'password' => 'password123',
        ]);

        $other = ProfesseurFactory::createOne([
            'email' => 'classcourses.other@example.com',
            'password' => 'password123',
        ]);

        $classe = ClasseFactory::createOne(['professeur' => $owner]);

        $token = $this->authenticateAndGetToken($other->getEmail(), 'password123');
        $this->get('/api/professor/classes/'.$classe->getId().'/courses', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('You are not the professor of this class', $this->getResponseContent());
    }
}
