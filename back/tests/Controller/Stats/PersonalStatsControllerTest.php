<?php

namespace App\Tests\Controller\Stats;

use App\Factory\ActiviteFactory;
use App\Factory\ClasseFactory;
use App\Factory\CompetenceFactory;
use App\Factory\EleveCompetenceFactory;
use App\Factory\EleveFactory;
use App\Factory\CoursFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Factory\QcmFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class PersonalStatsControllerTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    // ── progressions ─────────────────────────────────────────────────────────

    public function testProgressionsRequiresAuthentication(): void
    {
        $this->get('/api/my-stats/progressions');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testProgressionsForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get('/api/my-stats/progressions', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testProgressionsReturnsEmptyArrayWhenNoData(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/progressions', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testProgressionsReturnsCorrectStructure(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 75]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/progressions', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('title', $responseData[0]);
        $this->assertArrayHasKey('percentage', $responseData[0]);
        $this->assertIsInt($responseData[0]['percentage']);
        $this->assertSame(75, $responseData[0]['percentage']);
    }

    // ── competences ──────────────────────────────────────────────────────────

    public function testCompetencesRequiresAuthentication(): void
    {
        $this->get('/api/my-stats/competences');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCompetencesForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get('/api/my-stats/competences', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCompetencesReturnsEmptyArrayWhenNoCompetencesExist(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/competences', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testCompetencesReturnsCorrectStructure(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $competence = CompetenceFactory::createOne();
        EleveCompetenceFactory::createOne(['eleve' => $eleve, 'competence' => $competence]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/competences', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('matiere', $responseData[0]);
        $this->assertArrayHasKey('percentage', $responseData[0]);
        $this->assertIsNumeric($responseData[0]['percentage']);
    }

    // ── quiz-scores ──────────────────────────────────────────────────────────

    public function testQuizScoresRequiresAuthentication(): void
    {
        $this->get('/api/my-stats/quiz-scores');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testQuizScoresForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get('/api/my-stats/quiz-scores', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testQuizScoresReturnsEmptyArrayWhenNoData(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/quiz-scores', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testQuizScoresReturnsCorrectStructure(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours, 'type' => 'qcm']);
        QcmFactory::createOne(['activite' => $activite, 'gainPts' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/quiz-scores', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('label', $responseData[0]);
        $this->assertArrayHasKey('points', $responseData[0]);
        $this->assertIsInt($responseData[0]['points']);
        $this->assertSame(20, $responseData[0]['points']);
    }

    // ── badges ───────────────────────────────────────────────────────────────

    public function testBadgesRequiresAuthentication(): void
    {
        $this->get('/api/my-stats/badges');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBadgesForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get('/api/my-stats/badges', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testBadgesReturnsEmptyArrayWhenNoData(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/badges', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testBadgesReturnsCorrectStructure(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        ProgressionFactory::createOne(['eleve' => $eleve]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/badges', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('type', $responseData[0]);
        $this->assertArrayHasKey('count', $responseData[0]);
        $this->assertIsInt($responseData[0]['count']);
        $this->assertGreaterThan(0, $responseData[0]['count']);
    }

    // ── class-rank ───────────────────────────────────────────────────────────

    public function testClassRankRequiresAuthentication(): void
    {
        $this->get('/api/my-stats/class-rank');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testClassRankForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get('/api/my-stats/class-rank', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testClassRankReturnsNotFoundWhenNoProgressionData(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get('/api/my-stats/class-rank', $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testClassRankReturnsCorrectStructure(): void
    {
        $classe = ClasseFactory::createOne();
        $eleve = EleveFactory::createOne([
            'email' => 'eleve@example.com',
            'password' => 'password123',
            'classe' => $classe,
        ]);
        $other = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 80]);
        ProgressionFactory::createOne(['eleve' => $other, 'percentage' => 60]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/class-rank', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('myAverage', $responseData);
        $this->assertArrayHasKey('rank', $responseData);
        $this->assertArrayHasKey('total', $responseData);
        $this->assertArrayHasKey('percentile', $responseData);
        $this->assertSame(2, $responseData['total']);
        $this->assertSame(1, $responseData['rank']);
    }

    public function testClassRankWhenNotTopStudent(): void
    {
        $classe = ClasseFactory::createOne();
        $eleve = EleveFactory::createOne([
            'email' => 'low.eleve@example.com',
            'password' => 'password123',
            'classe' => $classe,
        ]);
        $topStudent = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 40]);
        ProgressionFactory::createOne(['eleve' => $topStudent, 'percentage' => 90]);
        $token = $this->authenticateAndGetToken('low.eleve@example.com', 'password123');

        $client = $this->get('/api/my-stats/class-rank', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(2, $responseData['rank']);
    }
}
