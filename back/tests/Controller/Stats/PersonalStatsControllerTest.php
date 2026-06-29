<?php

namespace App\Tests\Controller\Stats;

use App\Factory\ActiviteFactory;
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

        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');
        $this->get('/api/my-stats/quiz-scores', $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $responseData = $this->getRequestResponse();
        $this->assertCount(1, $responseData);
        $this->assertSame('Cours PHP – Q1', $responseData[0]['label']);
        $this->assertSame(20, $responseData[0]['points']);
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

    // ── badges-detail ────────────────────────────────────────────────────────

    public string $badges_detail_endpoint = '/api/my-stats/badges-detail';

    public function testBadgesDetailRequiresAuthentication(): void
    {
        $this->get($this->badges_detail_endpoint);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBadgesDetailForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get($this->badges_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testBadgesDetailReturnsEmptyArrayWhenNoProgressions(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->badges_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testBadgesDetailReturnsCorrectStructure(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $badge = BadgeFactory::createOne(['type' => 'or', 'label' => 'Or']);
        $cours = CoursFactory::createOne(['titre' => 'Cours PHP']);
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge, 'cours' => $cours, 'percentage' => 75]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->badges_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();

        $this->assertCount(1, $data);
        $this->assertSame('Cours PHP', $data[0]['courseTitle']);
        $this->assertSame('or', $data[0]['badgeType']);
        $this->assertSame('Or', $data[0]['badgeLabel']);
        $this->assertSame(75, $data[0]['percentage']);
        $this->assertArrayHasKey('courseId', $data[0]);
    }

    public function testBadgesDetailOrdersByPercentageDescending(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $badge = BadgeFactory::createOne();
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 30]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 100]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 60]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->badges_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();

        $this->assertCount(3, $data);
        $this->assertSame(100, $data[0]['percentage']);
        $this->assertSame(60, $data[1]['percentage']);
        $this->assertSame(30, $data[2]['percentage']);
    }

    public function testBadgesDetailOnlyReturnsCurrentStudentProgressions(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $other = EleveFactory::createOne();
        $badge = BadgeFactory::createOne();
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 50]);
        ProgressionFactory::createOne(['eleve' => $other, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 90]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->badges_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();

        $this->assertCount(1, $data);
        $this->assertSame(50, $data[0]['percentage']);
    }

    // ── competences-detail ────────────────────────────────────────────────────

    public string $competences_detail_endpoint = '/api/my-stats/competences-detail';

    public function testCompetencesDetailRequiresAuthentication(): void
    {
        $this->get($this->competences_detail_endpoint);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCompetencesDetailForbiddenForProfessor(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('prof@example.com', 'password123');

        $this->get($this->competences_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCompetencesDetailReturnsEmptyWhenNoProgressions(): void
    {
        EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->competences_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], $this->getRequestResponse());
    }

    public function testCompetencesDetailReturnsAcquiredFalseWhenNoEleveCompetence(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $matiere = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $cours = CoursFactory::createOne(['titre' => 'Algèbre', 'matiere' => $matiere]);
        $competence = CompetenceFactory::createOne(['nom' => 'Résoudre des équations', 'niveau' => 'débutant', 'cours' => $cours]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->competences_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();

        $this->assertCount(1, $data);
        $this->assertFalse($data[0]['acquired']);
        $this->assertSame('Résoudre des équations', $data[0]['nom']);
        $this->assertSame('débutant', $data[0]['niveau']);
        $this->assertSame('Algèbre', $data[0]['courseTitle']);
        $this->assertSame('Mathématiques', $data[0]['matiere']);
        $this->assertSame($cours->getId(), $data[0]["courseId"]);
        $this->assertSame($competence->getId(), $data[0]["id"]);
    }

    public function testCompetencesDetailReturnsAcquiredTrueWhenEleveCompetenceExists(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $matiere = MatiereFactory::createOne();
        $cours = CoursFactory::createOne(['matiere' => $matiere]);
        $competence = CompetenceFactory::createOne(['cours' => $cours]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);
        EleveCompetenceFactory::createOne(['eleve' => $eleve, 'competence' => $competence]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->competences_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();

        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['acquired']);
    }

    public function testCompetencesDetailExcludesCoursesWithoutProgression(): void
    {
        $eleve = EleveFactory::createOne(['email' => 'eleve@example.com', 'password' => 'password123']);
        $matiere = MatiereFactory::createOne();
        $coursWithProgression = CoursFactory::createOne(['matiere' => $matiere]);
        $coursWithoutProgression = CoursFactory::createOne(['matiere' => $matiere]);
        CompetenceFactory::createOne(['cours' => $coursWithProgression]);
        CompetenceFactory::createOne(['cours' => $coursWithoutProgression]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursWithProgression]);
        $token = $this->authenticateAndGetToken('eleve@example.com', 'password123');

        $this->get($this->competences_detail_endpoint, $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);
        $data = $this->getRequestResponse();

        $this->assertCount(1, $data);
        $this->assertNotNull($coursWithProgression->getId());
        $this->assertSame($coursWithProgression->getId(), $data[0]["courseId"]);
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
