<?php

namespace App\Tests\Controller\Stats;

use App\Factory\BadgeFactory;
use App\Factory\ClasseFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProfesseurFactory;
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

    public function testCompletionBySubjectRejectsNonProfessorCaller(): void
    {
        EleveFactory::createOne(['email' => 'stats.eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.eleve@example.com', 'password123');

        $this->get('/api/stats/completion-by-subject', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('Only professors can access this resource', $this->getResponseContent());
    }

    public function testCompletionBySubjectReturnsEmptyArrayWhenNoData(): void
    {
        ProfesseurFactory::createOne(['email' => 'stats.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.empty@example.com', 'password123');

        $this->get('/api/stats/completion-by-subject', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testCompletionBySubjectReturnsCorrectStructure(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'stats.data@example.com', 'password' => 'password123']);
        $matiere = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $cours = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiere]);
        ProgressionFactory::createOne(['cours' => $cours, 'percentage' => 60]);
        $token = $this->authenticateAndGetToken('stats.data@example.com', 'password123');

        $this->get('/api/stats/completion-by-subject', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('Mathématiques', $responseData[0]['subject']);
        $this->assertSame(60, $responseData[0]['average']);
    }

    public function testCompletionBySubjectExcludesCoursesFromOtherProfessors(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'stats.own@example.com', 'password' => 'password123']);
        $otherProf = ProfesseurFactory::createOne();
        $matiere = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $ownCours = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiere]);
        $otherCours = CoursFactory::createOne(['professeur' => $otherProf, 'matiere' => $matiere]);
        ProgressionFactory::createOne(['cours' => $ownCours, 'percentage' => 40]);
        ProgressionFactory::createOne(['cours' => $otherCours, 'percentage' => 100]);
        $token = $this->authenticateAndGetToken('stats.own@example.com', 'password123');

        $this->get('/api/stats/completion-by-subject', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame(40.0, (float) $responseData[0]['average']);
    }

    // ── active-students-per-class ────────────────────────────────────────────

    public function testActiveStudentsPerClassRequiresAuthentication(): void
    {
        $this->get('/api/stats/active-students-per-class');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testActiveStudentsPerClassRejectsNonProfessorCaller(): void
    {
        EleveFactory::createOne(['email' => 'stats.eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.eleve@example.com', 'password123');

        $this->get('/api/stats/active-students-per-class', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('Only professors can access this resource', $this->getResponseContent());
    }

    public function testActiveStudentsPerClassReturnsEmptyArrayWhenNoData(): void
    {
        ProfesseurFactory::createOne(['email' => 'stats.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.empty@example.com', 'password123');

        $this->get('/api/stats/active-students-per-class', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testActiveStudentsPerClassReturnsCorrectStructure(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'stats.data@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['nom' => '5ème A', 'professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve]);
        $token = $this->authenticateAndGetToken('stats.data@example.com', 'password123');

        $this->get('/api/stats/active-students-per-class', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('5ème A', $responseData[0]['classe']);
        $this->assertSame(1, $responseData[0]['count']);
    }

    public function testActiveStudentsPerClassExcludesClassesFromOtherProfessors(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'stats.own@example.com', 'password' => 'password123']);
        $otherProf = ProfesseurFactory::createOne();
        $ownClasse = ClasseFactory::createOne(['nom' => 'Own Class', 'professeur' => $prof]);
        $otherClasse = ClasseFactory::createOne(['nom' => 'Other Class', 'professeur' => $otherProf]);
        $ownEleve = EleveFactory::createOne(['classe' => $ownClasse]);
        $otherEleve = EleveFactory::createOne(['classe' => $otherClasse]);
        ProgressionFactory::createOne(['eleve' => $ownEleve]);
        ProgressionFactory::createOne(['eleve' => $otherEleve]);
        $token = $this->authenticateAndGetToken('stats.own@example.com', 'password123');

        $this->get('/api/stats/active-students-per-class', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('Own Class', $responseData[0]['classe']);
    }

    // ── badge-distribution ───────────────────────────────────────────────────

    public function testBadgeDistributionRequiresAuthentication(): void
    {
        $this->get('/api/stats/badge-distribution');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBadgeDistributionRejectsNonProfessorCaller(): void
    {
        EleveFactory::createOne(['email' => 'stats.eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.eleve@example.com', 'password123');

        $this->get('/api/stats/badge-distribution', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('Only professors can access this resource', $this->getResponseContent());
    }

    public function testBadgeDistributionReturnsEmptyArrayWhenNoData(): void
    {
        ProfesseurFactory::createOne(['email' => 'stats.empty@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.empty@example.com', 'password123');

        $this->get('/api/stats/badge-distribution', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testBadgeDistributionReturnsCorrectStructure(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'stats.data@example.com', 'password' => 'password123']);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $badge = BadgeFactory::createOne(['type' => 'bronze', 'label' => 'Débutant']);
        ProgressionFactory::createOne(['cours' => $cours, 'badge' => $badge]);
        $token = $this->authenticateAndGetToken('stats.data@example.com', 'password123');

        $this->get('/api/stats/badge-distribution', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('bronze', $responseData[0]['type']);
        $this->assertSame(1, $responseData[0]['count']);
    }

    public function testBadgeDistributionExcludesCoursesFromOtherProfessors(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'stats.own@example.com', 'password' => 'password123']);
        $otherProf = ProfesseurFactory::createOne();
        $ownCours = CoursFactory::createOne(['professeur' => $prof]);
        $otherCours = CoursFactory::createOne(['professeur' => $otherProf]);
        $badge = BadgeFactory::createOne(['type' => 'bronze']);
        ProgressionFactory::createOne(['cours' => $ownCours, 'badge' => $badge]);
        ProgressionFactory::createOne(['cours' => $otherCours, 'badge' => $badge]);
        $token = $this->authenticateAndGetToken('stats.own@example.com', 'password123');

        $this->get('/api/stats/badge-distribution', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame(1, $responseData[0]['count']);
    }

    // ── registrations ────────────────────────────────────────────────────────

    public function testRegistrationsRequiresAuthentication(): void
    {
        $this->get('/api/stats/registrations');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRegistrationsRejectsNonProfessorCaller(): void
    {
        EleveFactory::createOne(['email' => 'stats.eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('stats.eleve@example.com', 'password123');

        $this->get('/api/stats/registrations', $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('Only professors can access this resource', $this->getResponseContent());
    }

    public function testRegistrationsAlwaysReturnsEightWeeks(): void
    {
        ProfesseurFactory::createOne(['email' => 'stats.reg@example.com', 'password' => 'password123']);
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
        $prof = ProfesseurFactory::createOne(['email' => 'stats.reg@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        EleveFactory::createOne(['classe' => $classe]);
        $token = $this->authenticateAndGetToken('stats.reg@example.com', 'password123');

        $this->get('/api/stats/registrations', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();

        $currentWeek = (new \DateTimeImmutable())->format('Y-\WW');
        $currentEntry = current(array_filter($responseData, fn($row) => $row['week'] === $currentWeek));

        $this->assertNotFalse($currentEntry);
        $this->assertGreaterThanOrEqual(1, $currentEntry['count']);
    }

    public function testRegistrationsExcludesStudentsFromOtherProfessorsClasses(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'stats.own@example.com', 'password' => 'password123']);
        $otherProf = ProfesseurFactory::createOne();
        $ownClasse = ClasseFactory::createOne(['professeur' => $prof]);
        $otherClasse = ClasseFactory::createOne(['professeur' => $otherProf]);
        EleveFactory::createOne(['classe' => $ownClasse]);
        EleveFactory::createOne(['classe' => $otherClasse]);
        $token = $this->authenticateAndGetToken('stats.own@example.com', 'password123');

        $this->get('/api/stats/registrations', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();

        $currentWeek = (new \DateTimeImmutable())->format('Y-\WW');
        $currentEntry = current(array_filter($responseData, fn($row) => $row['week'] === $currentWeek));

        $this->assertNotFalse($currentEntry);
        $this->assertSame(1, $currentEntry['count']);
    }

    // ── best-students ────────────────────────────────────────────────────────

    public function testBestStudentsRequiresAuthentication(): void
    {
        $classe = ClasseFactory::createOne();

        $this->get('/api/stats/best-students/' . $classe->getId());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBestStudentsReturns404WhenClasseNotFound(): void
    {
        EleveFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $this->get('/api/stats/best-students/99999', $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testBestStudentsRejectsNonProfessorCaller(): void
    {
        $classe = ClasseFactory::createOne();
        EleveFactory::createOne(['email' => 'best.eleve@example.com', 'password' => 'password123', 'classe' => $classe]);
        $token = $this->authenticateAndGetToken('best.eleve@example.com', 'password123');

        $this->get('/api/stats/best-students/' . $classe->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('Only professors can access this resource', $this->getResponseContent());
    }

    public function testBestStudentsRejectsProfessorWhoDoesNotOwnTheClass(): void
    {
        $owner = ProfesseurFactory::createOne([
            'email' => 'best.owner@example.com',
            'password' => 'password123',
        ]);
        $other = ProfesseurFactory::createOne([
            'email' => 'best.other@example.com',
            'password' => 'password123',
        ]);
        $classe = ClasseFactory::createOne(['professeur' => $owner]);

        $token = $this->authenticateAndGetToken('best.other@example.com', 'password123');

        $this->get('/api/stats/best-students/' . $classe->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
        $this->assertStringContainsString('You are not the professor of this class', $this->getResponseContent());
    }

    public function testBestStudentsReturnsEmptyArrayWhenNoProgressions(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.empty@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        EleveFactory::createOne(['classe' => $classe]);
        $token = $this->authenticateAndGetToken('best.empty@example.com', 'password123');

        $this->get('/api/stats/best-students/' . $classe->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testBestStudentsDefaultsToFive(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        for ($i = 0; $i < 6; $i++) {
            $eleve = EleveFactory::createOne(['classe' => $classe]);
            ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => $i * 10]);
        }

        $this->get('/api/stats/best-students/' . $classe->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(5, $this->getRequestResponse());
    }

    public function testBestStudentsRespectsExplicitLimit(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        for ($i = 0; $i < 5; $i++) {
            $eleve = EleveFactory::createOne(['classe' => $classe]);
            ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => $i * 10]);
        }

        $this->get('/api/stats/best-students/' . $classe->getId() . '/3', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(3, $this->getRequestResponse());
    }

    public function testBestStudentsReturnsCorrectStructure(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['nom' => 'Terminale A', 'professeur' => $prof]);
        $eleve = EleveFactory::createOne([
            'name'      => 'Dupont',
            'firstname' => 'Alice',
            'classe'    => $classe,
        ]);
        $matiere = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $cours = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiere]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 80]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $this->get('/api/stats/best-students/' . $classe->getId() . '/1', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();
        $this->assertCount(1, $data);
        $this->assertSame(1, $data[0]['rank']);
        $this->assertSame('Dupont', $data[0]['name']);
        $this->assertSame('Alice', $data[0]['firstname']);
        $this->assertSame(80.0, (float) $data[0]['average']);
        $this->assertSame(0, $data[0]['completedCourses']);
        $this->assertSame(1, $data[0]['totalCourses']);
        $this->assertSame('Mathématiques', $data[0]['topSubject']);
        $this->assertArrayNotHasKey('classe', $data[0]);
    }

    public function testBestStudentsReturnsCompletedCoursesCount(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 100]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 100]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 60]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $this->get('/api/stats/best-students/' . $classe->getId() . '/1', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();
        $this->assertSame(2, $data[0]['completedCourses']);
        $this->assertSame(3, $data[0]['totalCourses']);
    }

    public function testBestStudentsReturnsTopSubject(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        $matiereA = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $matiereB = MatiereFactory::createOne(['libelle' => 'Français']);
        $coursA = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiereA]);
        $coursB = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiereB]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursA, 'percentage' => 95]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursB, 'percentage' => 40]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $this->get('/api/stats/best-students/' . $classe->getId() . '/1', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();
        $this->assertSame('Mathématiques', $data[0]['topSubject']);
    }

    public function testBestStudentsTopSubjectIsNullWhenNoProgressions(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        EleveFactory::createOne(['classe' => $classe]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $this->get('/api/stats/best-students/' . $classe->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testBestStudentsOnlyReturnsStudentsFromGivenClass(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $classeA = ClasseFactory::createOne(['professeur' => $prof]);
        $classeB = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        EleveFactory::createOne(['classe' => $classeA]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $eleveB = EleveFactory::createOne(['classe' => $classeB]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'cours' => $cours, 'percentage' => 95]);

        $this->get('/api/stats/best-students/' . $classeA->getId() . '/5', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testBestStudentsExcludesCoursesFromOtherProfessors(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $otherProf = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $ownCours = CoursFactory::createOne(['professeur' => $prof]);
        $otherCours = CoursFactory::createOne(['professeur' => $otherProf]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        // a progression can end up pointing to a course owned by another professor
        // (e.g. stale/inconsistent data) even though it is scoped to this classe
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $ownCours, 'classe' => $classe, 'percentage' => 40]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $otherCours, 'classe' => $classe, 'percentage' => 100]);

        $this->get('/api/stats/best-students/' . $classe->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();
        $this->assertCount(1, $data);
        $this->assertSame(40.0, (float) $data[0]['average']);
        $this->assertSame(1, $data[0]['totalCourses']);
    }

    public function testBestStudentsAreOrderedByAverageDescending(): void
    {
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $eleveA = EleveFactory::createOne(['classe' => $classe]);
        $eleveB = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleveA, 'cours' => $cours, 'percentage' => 30]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'cours' => $cours, 'percentage' => 90]);

        $this->get('/api/stats/best-students/' . $classe->getId() . '/2', $this->withToken($token));

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
        $prof = ProfesseurFactory::createOne(['email' => 'best.auth@example.com', 'password' => 'password123']);
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        EleveFactory::createOne(['classe' => $classe]);
        $token = $this->authenticateAndGetToken('best.auth@example.com', 'password123');

        $this->get('/api/stats/best-students/' . $classe->getId() . '/0', $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }
}
