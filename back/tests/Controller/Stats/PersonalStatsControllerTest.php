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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class PersonalStatsControllerTest extends WebTestCase
{
    use ResetDatabase;
    use AuthenticatesUsers;

    // ── progressions ─────────────────────────────────────────────────────────

    public function testProgressionsRequiresAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/my-stats/progressions');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testProgressionsForbiddenForProfessor(): void
    {
        $client = self::createClient();
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'prof@example.com', 'password123');

        $client->request('GET', '/api/my-stats/progressions', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testProgressionsReturnsEmptyArrayWhenNoData(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/progressions', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testProgressionsReturnsCorrectStructure(): void
    {
        $client = self::createClient();
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 75]);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/progressions', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

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
        $client = self::createClient();

        $client->request('GET', '/api/my-stats/competences');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCompetencesForbiddenForProfessor(): void
    {
        $client = self::createClient();
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'prof@example.com', 'password123');

        $client->request('GET', '/api/my-stats/competences', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCompetencesReturnsEmptyArrayWhenNoCompetencesExist(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/competences', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testCompetencesReturnsCorrectStructure(): void
    {
        $client = self::createClient();
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $competence = CompetenceFactory::createOne();
        EleveCompetenceFactory::createOne(['eleve' => $eleve, 'competence' => $competence]);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/competences', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

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
        $client = self::createClient();

        $client->request('GET', '/api/my-stats/quiz-scores');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testQuizScoresForbiddenForProfessor(): void
    {
        $client = self::createClient();
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'prof@example.com', 'password123');

        $client->request('GET', '/api/my-stats/quiz-scores', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testQuizScoresReturnsEmptyArrayWhenNoData(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/quiz-scores', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testQuizScoresReturnsCorrectStructure(): void
    {
        $client = self::createClient();
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours, 'type' => 'qcm']);
        QcmFactory::createOne(['activite' => $activite, 'gainPts' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/quiz-scores', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

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
        $client = self::createClient();

        $client->request('GET', '/api/my-stats/badges');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBadgesForbiddenForProfessor(): void
    {
        $client = self::createClient();
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'prof@example.com', 'password123');

        $client->request('GET', '/api/my-stats/badges', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testBadgesReturnsEmptyArrayWhenNoData(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/badges', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testBadgesReturnsCorrectStructure(): void
    {
        $client = self::createClient();
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        ProgressionFactory::createOne(['eleve' => $eleve]);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/badges', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

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
        $client = self::createClient();

        $client->request('GET', '/api/my-stats/class-rank');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testClassRankForbiddenForProfessor(): void
    {
        $client = self::createClient();
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'prof@example.com', 'password123');

        $client->request('GET', '/api/my-stats/class-rank', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testClassRankReturnsNotFoundWhenNoProgressionData(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/class-rank', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testClassRankReturnsCorrectStructure(): void
    {
        $client = self::createClient();
        $classe = ClasseFactory::createOne();
        $eleve = EleveFactory::createOne([
            'email' => 'eleve@example.com',
            'password' => 'password123',
            'classe' => $classe,
        ]);
        $other = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 80]);
        ProgressionFactory::createOne(['eleve' => $other, 'percentage' => 60]);
        $token = $this->authenticateAndGetToken($client, 'eleve@example.com', 'password123');

        $client->request('GET', '/api/my-stats/class-rank', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('myAverage', $responseData);
        $this->assertArrayHasKey('rank', $responseData);
        $this->assertArrayHasKey('total', $responseData);
        $this->assertArrayHasKey('percentile', $responseData);
        $this->assertSame(2, $responseData['total']);
        $this->assertSame(1, $responseData['rank']);
    }

}
