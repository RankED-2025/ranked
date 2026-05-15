<?php

namespace App\Tests\Controller\Stats;

use App\Factory\EleveFactory;
use App\Factory\ProgressionFactory;
use App\Tests\Traits\AuthenticatesUsers;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class StatsControllerTest extends WebTestCase
{
    use ResetDatabase;
    use AuthenticatesUsers;

    // ── completion-by-subject ────────────────────────────────────────────────

    public function testCompletionBySubjectRequiresAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/stats/completion-by-subject');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCompletionBySubjectReturnsEmptyArrayWhenNoData(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'stats.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'stats.empty@example.com', 'password123');

        $client->request('GET', '/api/stats/completion-by-subject', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testCompletionBySubjectReturnsCorrectStructure(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'stats.data@example.com', 'password' => 'password123']);
        ProgressionFactory::createOne();
        $token = $this->authenticateAndGetToken($client, 'stats.data@example.com', 'password123');

        $client->request('GET', '/api/stats/completion-by-subject', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('subject', $responseData[0]);
        $this->assertArrayHasKey('average', $responseData[0]);
        $this->assertIsNumeric($responseData[0]['average']);
    }

    // ── active-students-per-class ────────────────────────────────────────────

    public function testActiveStudentsPerClassRequiresAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/stats/active-students-per-class');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testActiveStudentsPerClassReturnsEmptyArrayWhenNoData(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'stats.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'stats.empty@example.com', 'password123');

        $client->request('GET', '/api/stats/active-students-per-class', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testActiveStudentsPerClassReturnsCorrectStructure(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'stats.data@example.com', 'password' => 'password123']);
        ProgressionFactory::createOne();
        $token = $this->authenticateAndGetToken($client, 'stats.data@example.com', 'password123');

        $client->request('GET', '/api/stats/active-students-per-class', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('classe', $responseData[0]);
        $this->assertArrayHasKey('count', $responseData[0]);
        $this->assertIsInt($responseData[0]['count']);
        $this->assertGreaterThan(0, $responseData[0]['count']);
    }

    // ── badge-distribution ───────────────────────────────────────────────────

    public function testBadgeDistributionRequiresAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/stats/badge-distribution');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBadgeDistributionReturnsEmptyArrayWhenNoData(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'stats.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'stats.empty@example.com', 'password123');

        $client->request('GET', '/api/stats/badge-distribution', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));
    }

    public function testBadgeDistributionReturnsCorrectStructure(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'stats.data@example.com', 'password' => 'password123']);
        ProgressionFactory::createOne();
        $token = $this->authenticateAndGetToken($client, 'stats.data@example.com', 'password123');

        $client->request('GET', '/api/stats/badge-distribution', [], [], [
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

    // ── registrations ────────────────────────────────────────────────────────

    public function testRegistrationsRequiresAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/stats/registrations');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRegistrationsAlwaysReturnsEightWeeks(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'stats.reg@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'stats.reg@example.com', 'password123');

        $client->request('GET', '/api/stats/registrations', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertCount(8, $responseData);
        $this->assertArrayHasKey('week', $responseData[0]);
        $this->assertArrayHasKey('count', $responseData[0]);
    }

    public function testRegistrationsCountsCurrentWeekRegistrations(): void
    {
        $client = self::createClient();
        EleveFactory::createOne(['email' => 'stats.reg@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken($client, 'stats.reg@example.com', 'password123');

        $client->request('GET', '/api/stats/registrations', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $currentWeek = (new \DateTimeImmutable())->format('Y-\WW');
        $currentEntry = current(array_filter($responseData, fn($row) => $row['week'] === $currentWeek));

        $this->assertNotFalse($currentEntry);
        $this->assertGreaterThanOrEqual(1, $currentEntry['count']);
    }

}
