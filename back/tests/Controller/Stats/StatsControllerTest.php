<?php

namespace App\Tests\Controller\Stats;

use App\Factory\BadgeFactory;
use App\Factory\ClasseFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class StatsControllerTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers;

    // ── completion-by-subject ────────────────────────────────────────────────

    public function testCompletionBySubjectRequiresAuthentication(): void
    {
        $this->get('/api/stats/completion-by-subject');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCompletionBySubjectReturnsEmptyArrayWhenNoData(): void
    {
        EleveFactory::createOne(['email' => 'stats.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.empty@example.com', 'password123');

        $this->get('/api/stats/completion-by-subject', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testCompletionBySubjectReturnsCorrectStructure(): void
    {
        EleveFactory::createOne(['email' => 'stats.data@example.com', 'password' => 'password123']);
        $matiere = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $cours = CoursFactory::createOne(['matiere' => $matiere]);
        ProgressionFactory::createOne(['cours' => $cours, 'percentage' => 60]);
        $token = $this->authenticateAndGetToken('stats.data@example.com', 'password123');

        $this->get('/api/stats/completion-by-subject', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('Mathématiques', $responseData[0]['subject']);
        $this->assertSame(60, $responseData[0]['average']);
    }

    // ── active-students-per-class ────────────────────────────────────────────

    public function testActiveStudentsPerClassRequiresAuthentication(): void
    {
        $this->get('/api/stats/active-students-per-class');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testActiveStudentsPerClassReturnsEmptyArrayWhenNoData(): void
    {
        EleveFactory::createOne(['email' => 'stats.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.empty@example.com', 'password123');

        $this->get('/api/stats/active-students-per-class', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testActiveStudentsPerClassReturnsCorrectStructure(): void
    {
        $classe = ClasseFactory::createOne(['nom' => '5ème A']);
        $eleveAuth = EleveFactory::createOne(['email' => 'stats.data@example.com', 'password' => 'password123', 'classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleveAuth]);
        $token = $this->authenticateAndGetToken('stats.data@example.com', 'password123');

        $this->get('/api/stats/active-students-per-class', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('5ème A', $responseData[0]['classe']);
        $this->assertSame(1, $responseData[0]['count']);
    }

    // ── badge-distribution ───────────────────────────────────────────────────

    public function testBadgeDistributionRequiresAuthentication(): void
    {
        $this->get('/api/stats/badge-distribution');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBadgeDistributionReturnsEmptyArrayWhenNoData(): void
    {
        EleveFactory::createOne(['email' => 'stats.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.empty@example.com', 'password123');

        $this->get('/api/stats/badge-distribution', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testBadgeDistributionReturnsCorrectStructure(): void
    {
        EleveFactory::createOne(['email' => 'stats.data@example.com', 'password' => 'password123']);
        $badge = BadgeFactory::createOne(['type' => 'bronze', 'label' => 'Débutant']);
        ProgressionFactory::createOne(['badge' => $badge]);
        $token = $this->authenticateAndGetToken('stats.data@example.com', 'password123');

        $this->get('/api/stats/badge-distribution', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('bronze', $responseData[0]['type']);
        $this->assertSame(1, $responseData[0]['count']);
    }

    // ── registrations ────────────────────────────────────────────────────────

    public function testRegistrationsRequiresAuthentication(): void
    {
        $this->get('/api/stats/registrations');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRegistrationsAlwaysReturnsEightWeeks(): void
    {
        EleveFactory::createOne(['email' => 'stats.reg@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.reg@example.com', 'password123');

        $this->get('/api/stats/registrations', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertIsArray($responseData);
        $this->assertCount(8, $responseData);
        $this->assertArrayHasKey('week', $responseData[0]);
        $this->assertArrayHasKey('count', $responseData[0]);
    }

    public function testRegistrationsCountsCurrentWeekRegistrations(): void
    {
        EleveFactory::createOne(['email' => 'stats.reg@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.reg@example.com', 'password123');

        $this->get('/api/stats/registrations', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();

        $currentWeek = (new \DateTimeImmutable())->format('Y-\WW');
        $currentEntry = current(array_filter($responseData, fn($row) => $row['week'] === $currentWeek));

        $this->assertNotFalse($currentEntry);
        $this->assertGreaterThanOrEqual(1, $currentEntry['count']);
    }

    // ── best-students ────────────────────────────────────────────────────────

    public function testBestStudentsRequiresAuthentication(): void
    {
        $this->get('/api/stats/best-students');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBestStudentsReturnsEmptyArrayWhenNoProgressions(): void
    {
        EleveFactory::createOne(['email' => 'best.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('best.empty@example.com', 'password123');

        $this->get('/api/stats/best-students', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testBestStudentsDefaultsToFive(): void
    {
        EleveFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        for ($i = 0; $i < 6; $i++) {
            $eleve = EleveFactory::createOne();
            ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => $i * 10]);
        }

        $this->get('/api/stats/best-students', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(5, $this->getRequestResponse());
    }

    public function testBestStudentsRespectsExplicitLimit(): void
    {
        EleveFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        for ($i = 0; $i < 5; $i++) {
            $eleve = EleveFactory::createOne();
            ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => $i * 10]);
        }

        $this->get('/api/stats/best-students/3', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(3, $this->getRequestResponse());
    }

    public function testBestStudentsReturnsCorrectStructure(): void
    {
        $classe = ClasseFactory::createOne(['nom' => 'Terminale A']);
        $eleve = EleveFactory::createOne([
            'email'     => 'best.auth@example.com',
            'password'  => 'password123',
            'name'      => 'Dupont',
            'firstname' => 'Alice',
            'classe'    => $classe,
        ]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 80]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $this->get('/api/stats/best-students/1', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();
        $this->assertCount(1, $data);
        $this->assertSame(1, $data[0]['rank']);
        $this->assertSame('Dupont', $data[0]['name']);
        $this->assertSame('Alice', $data[0]['firstname']);
        $this->assertSame('Terminale A', $data[0]['classe']);
        $this->assertEquals(80.0, $data[0]['average']);
    }

    public function testBestStudentsNullClasseIsIncluded(): void
    {
        EleveFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $eleveNoClasse = EleveFactory::createOne(['classe' => null]);
        ProgressionFactory::createOne(['eleve' => $eleveNoClasse, 'percentage' => 90]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $this->get('/api/stats/best-students/1', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();
        $this->assertCount(1, $data);
        $this->assertNull($data[0]['classe']);
    }

    public function testBestStudentsAreOrderedByAverageDescending(): void
    {
        EleveFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $eleveA = EleveFactory::createOne();
        $eleveB = EleveFactory::createOne();
        ProgressionFactory::createOne(['eleve' => $eleveA, 'percentage' => 30]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'percentage' => 90]);

        $this->get('/api/stats/best-students/2', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();
        $this->assertCount(2, $data);

        $this->assertSame(1, $data[0]['rank']);
        $this->assertSame(2, $data[1]['rank']);

        $this->assertSame(90.0, (float) $data[0]['average']);
        $this->assertSame(30.0, (float) $data[1]['average']);
    }

    public function testBestStudentsRejectsZeroLimit(): void
    {
        EleveFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $this->get('/api/stats/best-students/0', $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }
}
