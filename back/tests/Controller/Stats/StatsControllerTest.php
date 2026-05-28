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
}
