<?php

namespace App\Tests\Controller\Stats;

use App\Factory\ActiviteFactory;
use App\Factory\ActiviteProgressionFactory;
use App\Factory\BadgeFactory;
use App\Factory\ClasseFactory;
use App\Factory\CompetenceFactory;
use App\Factory\EleveCompetenceFactory;
use App\Factory\EleveFactory;
use App\Factory\CoursFactory;
use App\Factory\MatiereFactory;
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

        $this->get('/api/my-stats/progressions', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testProgressionsReturnsCorrectStructure(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        ProgressionFactory::createOne([
            'eleve' => $eleve,
            'percentage' => 75,
            'cours' => CoursFactory::createOne(['titre' => 'Cours Maths']),
        ]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get('/api/my-stats/progressions', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('Cours Maths', $responseData[0]['title']);
        $this->assertSame(75, $responseData[0]['percentage']);
    }

    // ── competences ──────────────────────────────────────────────────────────

    public $mycompetences_endpoint = "/api/my-stats/competences";

    public function testCompetencesRequiresAuthentication(): void
    {
        $this->get($this->mycompetences_endpoint);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCompetencesForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get($this->mycompetences_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCompetencesReturnsEmptyArrayWhenNoCompetencesExist(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->mycompetences_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testCompetencesReturnsCorrectStructure(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $matiere = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $competence = CompetenceFactory::createOne(['cours' => CoursFactory::createOne(['matiere' => $matiere])]);
        EleveCompetenceFactory::createOne(['eleve' => $eleve, 'competence' => $competence]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->mycompetences_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('Mathématiques', $responseData[0]['matiere']);
        $this->assertSame(100, $responseData[0]['percentage']);
    }

    // ── quiz-scores ──────────────────────────────────────────────────────────

    public $quiz_score_endpoints = '/api/my-stats/quiz-scores';

    public function testQuizScoresRequiresAuthentication(): void
    {
        $this->get($this->quiz_score_endpoints);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testQuizScoresForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get($this->quiz_score_endpoints, $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testQuizScoresReturnsEmptyArrayWhenNoData(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->quiz_score_endpoints, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testQuizScoresReturnsCorrectStructure(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $cours = CoursFactory::createOne(['titre' => 'Cours PHP']);
        $activite = ActiviteFactory::createOne(['cours' => $cours, 'type' => 'qcm']);
        QcmFactory::createOne(['activite' => $activite, 'gainPts' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);
        ActiviteProgressionFactory::createOne([
            'eleve' => $eleve,
            'activite' => $activite,
            'completedAt' => new \DateTimeImmutable(),
            'score' => 3,
            'total' => 4,
            'earnedPts' => 15,
        ]);

        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');
        $this->get('/api/my-stats/quiz-scores', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('Cours PHP – Q1', $responseData[0]['label']);
        $this->assertSame(15, $responseData[0]['points']);
    }

    public function testQuizScoresExcludesUnattemptedQuizzes(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $cours = CoursFactory::createOne(['titre' => 'Cours PHP']);
        $activite = ActiviteFactory::createOne(['cours' => $cours, 'type' => 'qcm']);
        QcmFactory::createOne(['activite' => $activite, 'gainPts' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);

        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');
        $this->get('/api/my-stats/quiz-scores', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    // ── badges ───────────────────────────────────────────────────────────────

    public $badge_endpoint = "/api/my-stats/badges";

    public function testBadgesRequiresAuthentication(): void
    {
        $this->get($this->badge_endpoint);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBadgesForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get($this->badge_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testBadgesReturnsEmptyArrayWhenNoData(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->badge_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testBadgesReturnsCorrectStructure(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $badge = BadgeFactory::createOne(['type' => 'bronze', 'label' => 'Débutant']);
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get('/api/my-stats/badges', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('bronze', $responseData[0]['type']);
        $this->assertSame(1, $responseData[0]['count']);
    }

    // ── class-rank ───────────────────────────────────────────────────────────

    public $class_rank_endpoint = "/api/my-stats/class-rank";

    public function testClassRankRequiresAuthentication(): void
    {
        $this->get($this->class_rank_endpoint);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testClassRankForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get($this->class_rank_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testClassRankReturnsNotFoundWhenNoProgressionData(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->class_rank_endpoint, $this->withToken($token));

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

        $this->get($this->class_rank_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertSame(80, $responseData['myAverage']);
        $this->assertSame(1, $responseData['rank']);
        $this->assertSame(2, $responseData['total']);
        $this->assertSame(100, $responseData['percentile']);
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

        $this->get($this->class_rank_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertSame(2, $responseData['rank']);
    }
}
