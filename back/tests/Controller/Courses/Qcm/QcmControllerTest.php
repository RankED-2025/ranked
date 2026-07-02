<?php

namespace App\Tests\Controller\Courses\Qcm;

use App\Factory\ActiviteFactory;
use App\Factory\ActiviteProgressionFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Factory\QcmFactory;
use App\Factory\QuestionFactory;
use App\Factory\ReponseFactory;
use App\Repository\ActiviteProgressionRepository;
use App\Repository\ProgressionRepository;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class QcmControllerTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers, GetsContainerServices;

    /**
     * @return array<string, mixed>
     */
    private function createQuiz(string $email, int $gainPts = 20, bool $enrolled = true): array
    {
        $eleve = EleveFactory::createOne(['email' => $email, 'password' => 'password123']);
        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours, 'type' => 'qcm']);
        $qcm = QcmFactory::createOne(['activite' => $activite, 'gainPts' => $gainPts]);

        $q1 = QuestionFactory::createOne(['qcm' => $qcm, 'enonce' => 'Question 1 ?']);
        $q1Correct = ReponseFactory::createOne(['question' => $q1, 'isCorrect' => true, 'texte' => 'Bonne 1']);
        $q1Wrong = ReponseFactory::createOne(['question' => $q1, 'isCorrect' => false, 'texte' => 'Mauvaise 1']);

        $q2 = QuestionFactory::createOne(['qcm' => $qcm, 'enonce' => 'Question 2 ?']);
        $q2Correct = ReponseFactory::createOne(['question' => $q2, 'isCorrect' => true, 'texte' => 'Bonne 2']);
        $q2Wrong = ReponseFactory::createOne(['question' => $q2, 'isCorrect' => false, 'texte' => 'Mauvaise 2']);

        if ($enrolled) {
            ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 0]);
        }

        return compact('eleve', 'cours', 'activite', 'qcm', 'q1', 'q1Correct', 'q1Wrong', 'q2', 'q2Correct', 'q2Wrong');
    }

    public function testShowWithoutAuthentication(): void
    {
        $activite = ActiviteFactory::createOne(['type' => 'qcm']);

        $this->get('/api/qcm/'.$activite->getId());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testShowAsProfessorForbidden(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof.qcm-show@example.com', 'password' => 'password123']);
        $ctx = $this->createQuiz('student.qcm-show-prof@example.com');

        $token = $this->authenticateAndGetToken('prof.qcm-show@example.com', 'password123');

        $this->get('/api/qcm/'.$ctx['activite']->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowNonQuizActivityReturns404(): void
    {
        EleveFactory::createOne(['email' => 'student.qcm-notquiz@example.com', 'password' => 'password123']);
        $activite = ActiviteFactory::createOne(['type' => 'contenu']);

        $token = $this->authenticateAndGetToken('student.qcm-notquiz@example.com', 'password123');

        $this->get('/api/qcm/'.$activite->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowNotEnrolledForbidden(): void
    {
        $ctx = $this->createQuiz('student.qcm-show-noenroll@example.com', 20, false);

        $token = $this->authenticateAndGetToken('student.qcm-show-noenroll@example.com', 'password123');

        $this->get('/api/qcm/'.$ctx['activite']->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowReturnsQuestionsWithoutCorrectAnswers(): void
    {
        $ctx = $this->createQuiz('student.qcm-show-ok@example.com', 30);

        $token = $this->authenticateAndGetToken('student.qcm-show-ok@example.com', 'password123');

        $this->get('/api/qcm/'.$ctx['activite']->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $data = $this->getRequestResponse();
        $this->assertFalse($data['locked']);
        $this->assertSame(30, $data['gainPts']);
        $this->assertCount(2, $data['questions']);

        foreach ($data['questions'] as $question) {
            $this->assertArrayHasKey('enonce', $question);
            $this->assertNotEmpty($question['reponses']);
            foreach ($question['reponses'] as $reponse) {
                $this->assertArrayHasKey('texte', $reponse);
                $this->assertArrayNotHasKey('isCorrect', $reponse);
            }
        }
    }

    public function testShowLockedAfterAttempt(): void
    {
        $ctx = $this->createQuiz('student.qcm-show-locked@example.com');

        ActiviteProgressionFactory::createOne([
            'eleve' => $ctx['eleve'],
            'activite' => $ctx['activite'],
            'completedAt' => new \DateTimeImmutable(),
            'score' => 1,
            'total' => 2,
            'earnedPts' => 10,
        ]);

        $token = $this->authenticateAndGetToken('student.qcm-show-locked@example.com', 'password123');

        $this->get('/api/qcm/'.$ctx['activite']->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $data = $this->getRequestResponse();
        $this->assertTrue($data['locked']);
        $this->assertSame(1, $data['result']['score']);
        $this->assertSame(2, $data['result']['total']);
        $this->assertSame(10, $data['result']['earnedPts']);
        $this->assertArrayNotHasKey('questions', $data);
    }

    public function testSubmitAllCorrectAwardsFullPoints(): void
    {
        $ctx = $this->createQuiz('student.qcm-submit-full@example.com', 20);

        $token = $this->authenticateAndGetToken('student.qcm-submit-full@example.com', 'password123');

        $answers = [
            (string) $ctx['q1']->getId() => $ctx['q1Correct']->getId(),
            (string) $ctx['q2']->getId() => $ctx['q2Correct']->getId(),
        ];

        $this->post('/api/qcm/'.$ctx['activite']->getId().'/submit', ['answers' => $answers], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $data = $this->getRequestResponse();
        $this->assertSame(2, $data['score']);
        $this->assertSame(2, $data['total']);
        $this->assertSame(20, $data['earnedPts']);

        $progression = $this->getService(ActiviteProgressionRepository::class)->findOneBy([
            'eleve' => $ctx['eleve']->_real(),
            'activite' => $ctx['activite']->_real(),
        ]);

        $this->assertNotNull($progression);
        $this->assertSame(2, $progression->getScore());
        $this->assertSame(20, $progression->getEarnedPts());
        $this->assertNotNull($progression->getCompletedAt());

        $courseProgression = $this->getService(ProgressionRepository::class)->findOneBy([
            'eleve' => $ctx['eleve']->_real(),
            'cours' => $ctx['cours']->_real(),
        ]);
        $this->assertSame(100, $courseProgression->getPercentage());
    }

    public function testSubmitPartialAwardsProportionalPoints(): void
    {
        $ctx = $this->createQuiz('student.qcm-submit-partial@example.com', 20);

        $token = $this->authenticateAndGetToken('student.qcm-submit-partial@example.com', 'password123');

        $answers = [
            (string) $ctx['q1']->getId() => $ctx['q1Correct']->getId(),
            (string) $ctx['q2']->getId() => $ctx['q2Wrong']->getId(),
        ];

        $this->post('/api/qcm/'.$ctx['activite']->getId().'/submit', ['answers' => $answers], $this->withToken($token));

        $this->assertResponseStatusCodeSame(200);

        $data = $this->getRequestResponse();
        $this->assertSame(1, $data['score']);
        $this->assertSame(2, $data['total']);
        $this->assertSame(10, $data['earnedPts']);
    }

    public function testSubmitTwiceReturnsConflict(): void
    {
        $ctx = $this->createQuiz('student.qcm-submit-twice@example.com');

        ActiviteProgressionFactory::createOne([
            'eleve' => $ctx['eleve'],
            'activite' => $ctx['activite'],
            'completedAt' => new \DateTimeImmutable(),
            'score' => 2,
            'total' => 2,
            'earnedPts' => 20,
        ]);

        $token = $this->authenticateAndGetToken('student.qcm-submit-twice@example.com', 'password123');

        $answers = [
            (string) $ctx['q1']->getId() => $ctx['q1Correct']->getId(),
            (string) $ctx['q2']->getId() => $ctx['q2Correct']->getId(),
        ];

        $this->post('/api/qcm/'.$ctx['activite']->getId().'/submit', ['answers' => $answers], $this->withToken($token));

        $this->assertResponseStatusCodeSame(409);
    }

    public function testSubmitWithMissingAnswerReturnsBadRequest(): void
    {
        $ctx = $this->createQuiz('student.qcm-submit-missing@example.com');

        $token = $this->authenticateAndGetToken('student.qcm-submit-missing@example.com', 'password123');

        $answers = [
            (string) $ctx['q1']->getId() => $ctx['q1Correct']->getId(),
        ];

        $this->post('/api/qcm/'.$ctx['activite']->getId().'/submit', ['answers' => $answers], $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testSubmitWithInvalidAnswerReturnsBadRequest(): void
    {
        $ctx = $this->createQuiz('student.qcm-submit-invalid@example.com');

        $token = $this->authenticateAndGetToken('student.qcm-submit-invalid@example.com', 'password123');

        $answers = [
            (string) $ctx['q1']->getId() => 999999,
            (string) $ctx['q2']->getId() => $ctx['q2Correct']->getId(),
        ];

        $this->post('/api/qcm/'.$ctx['activite']->getId().'/submit', ['answers' => $answers], $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testSubmitWithoutAnswersReturnsBadRequest(): void
    {
        $ctx = $this->createQuiz('student.qcm-submit-noanswers@example.com');

        $token = $this->authenticateAndGetToken('student.qcm-submit-noanswers@example.com', 'password123');

        $this->post('/api/qcm/'.$ctx['activite']->getId().'/submit', [], $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }
}
