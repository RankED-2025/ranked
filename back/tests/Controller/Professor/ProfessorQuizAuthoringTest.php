<?php

namespace App\Tests\Controller\Professor;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProfesseurFactory;
use App\Tests\Traits\AuthenticatesUsers;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProfessorQuizAuthoringTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, AuthenticatesUsers, GetsContainerServices;

    /**
     * @param array<int, mixed> $questions
     * @return array<string, mixed>
     */
    private function qcmActivity(array $questions, int $gainPts = 15): array
    {
        return [
            'type'  => 'qcm',
            'ordre' => 1,
            'qcm'   => [
                'gainPts'   => $gainPts,
                'questions' => $questions,
            ],
        ];
    }

    private function authenticateProfessorWithCourse(string $email): array
    {
        $prof = ProfesseurFactory::createOne(['email' => $email, 'password' => 'password123']);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $token = $this->authenticateAndGetToken($email, 'password123');

        return [$prof, $cours, $token];
    }

    public function testEditPersistsQuestionsAndAnswers(): void
    {
        [, $cours, $token] = $this->authenticateProfessorWithCourse('prof.quiz-persist@example.com');

        $payload = [
            'activites' => [
                $this->qcmActivity([
                    [
                        'enonce'   => 'Capitale de la France ?',
                        'reponses' => [
                            ['texte' => 'Paris', 'isCorrect' => true],
                            ['texte' => 'Lyon', 'isCorrect' => false],
                        ],
                    ],
                ]),
            ],
        ];

        $this->post('/api/professor/courses/edit/'.$cours->getId(), $payload, $this->withToken($token));
        $this->assertResponseStatusCodeSame(200);

        $this->get('/api/professor/courses/'.$cours->getId(), $this->withToken($token));
        $this->assertResponseStatusCodeSame(200);

        $data = $this->getRequestResponse();
        $this->assertCount(1, $data['activites']);

        $qcm = $data['activites'][0]['qcm'];
        $this->assertSame(15, $qcm['gainPts']);
        $this->assertCount(1, $qcm['questions']);

        $question = $qcm['questions'][0];
        $this->assertSame('Capitale de la France ?', $question['enonce']);
        $this->assertCount(2, $question['reponses']);

        $correct = array_values(array_filter($question['reponses'], fn($r) => $r['isCorrect'] === true));
        $this->assertCount(1, $correct);
        $this->assertSame('Paris', $correct[0]['texte']);
    }

    public function testEditRejectsQuestionWithoutCorrectAnswer(): void
    {
        [, $cours, $token] = $this->authenticateProfessorWithCourse('prof.quiz-nocorrect@example.com');

        $payload = [
            'activites' => [
                $this->qcmActivity([
                    [
                        'enonce'   => 'Question sans bonne réponse ?',
                        'reponses' => [
                            ['texte' => 'A', 'isCorrect' => false],
                            ['texte' => 'B', 'isCorrect' => false],
                        ],
                    ],
                ]),
            ],
        ];

        $this->post('/api/professor/courses/edit/'.$cours->getId(), $payload, $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testEditRejectsQuestionWithMultipleCorrectAnswers(): void
    {
        [, $cours, $token] = $this->authenticateProfessorWithCourse('prof.quiz-multicorrect@example.com');

        $payload = [
            'activites' => [
                $this->qcmActivity([
                    [
                        'enonce'   => 'Question à deux bonnes réponses ?',
                        'reponses' => [
                            ['texte' => 'A', 'isCorrect' => true],
                            ['texte' => 'B', 'isCorrect' => true],
                        ],
                    ],
                ]),
            ],
        ];

        $this->post('/api/professor/courses/edit/'.$cours->getId(), $payload, $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testEditRejectsQuestionWithTooFewAnswers(): void
    {
        [, $cours, $token] = $this->authenticateProfessorWithCourse('prof.quiz-fewanswers@example.com');

        $payload = [
            'activites' => [
                $this->qcmActivity([
                    [
                        'enonce'   => 'Une seule réponse ?',
                        'reponses' => [
                            ['texte' => 'A', 'isCorrect' => true],
                        ],
                    ],
                ]),
            ],
        ];

        $this->post('/api/professor/courses/edit/'.$cours->getId(), $payload, $this->withToken($token));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testEditUpdatesAndRemovesQuestions(): void
    {
        [, $cours, $token] = $this->authenticateProfessorWithCourse('prof.quiz-sync@example.com');

        $createPayload = [
            'activites' => [
                $this->qcmActivity([
                    [
                        'enonce'   => 'Question 1 ?',
                        'reponses' => [
                            ['texte' => 'A', 'isCorrect' => true],
                            ['texte' => 'B', 'isCorrect' => false],
                        ],
                    ],
                    [
                        'enonce'   => 'Question 2 ?',
                        'reponses' => [
                            ['texte' => 'C', 'isCorrect' => true],
                            ['texte' => 'D', 'isCorrect' => false],
                        ],
                    ],
                ]),
            ],
        ];

        $this->post('/api/professor/courses/edit/'.$cours->getId(), $createPayload, $this->withToken($token));
        $this->assertResponseStatusCodeSame(200);

        $this->get('/api/professor/courses/'.$cours->getId(), $this->withToken($token));
        $created = $this->getRequestResponse();

        $activite = $created['activites'][0];
        $qcm = $activite['qcm'];
        $keptQuestion = $qcm['questions'][0];

        $updatePayload = [
            'activites' => [
                [
                    'id'    => $activite['id'],
                    'type'  => 'qcm',
                    'ordre' => 1,
                    'qcm'   => [
                        'id'        => $qcm['id'],
                        'gainPts'   => 15,
                        'questions' => [
                            [
                                'id'       => $keptQuestion['id'],
                                'enonce'   => 'Question 1 modifiée ?',
                                'reponses' => [
                                    ['id' => $keptQuestion['reponses'][0]['id'], 'texte' => 'A', 'isCorrect' => true],
                                    ['id' => $keptQuestion['reponses'][1]['id'], 'texte' => 'B', 'isCorrect' => false],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->post('/api/professor/courses/edit/'.$cours->getId(), $updatePayload, $this->withToken($token));
        $this->assertResponseStatusCodeSame(200);

        $this->get('/api/professor/courses/'.$cours->getId(), $this->withToken($token));
        $updated = $this->getRequestResponse();

        $questions = $updated['activites'][0]['qcm']['questions'];
        $this->assertCount(1, $questions);
        $this->assertSame('Question 1 modifiée ?', $questions[0]['enonce']);
    }

    public function testGetCourseContentForbiddenForEleve(): void
    {
        EleveFactory::createOne(['email' => 'eleve.quiz-read@example.com', 'password' => 'password123']);
        $cours = CoursFactory::createOne();

        $token = $this->authenticateAndGetToken('eleve.quiz-read@example.com', 'password123');

        $this->get('/api/professor/courses/'.$cours->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetCourseContentNotOwnerForbidden(): void
    {
        $owner = ProfesseurFactory::createOne(['email' => 'prof.owner@example.com', 'password' => 'password123']);
        ProfesseurFactory::createOne(['email' => 'prof.intruder@example.com', 'password' => 'password123']);
        $cours = CoursFactory::createOne(['professeur' => $owner]);

        $token = $this->authenticateAndGetToken('prof.intruder@example.com', 'password123');

        $this->get('/api/professor/courses/'.$cours->getId(), $this->withToken($token));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetCourseContentNotFound(): void
    {
        ProfesseurFactory::createOne(['email' => 'prof.read-404@example.com', 'password' => 'password123']);

        $token = $this->authenticateAndGetToken('prof.read-404@example.com', 'password123');

        $this->get('/api/professor/courses/999999', $this->withToken($token));

        $this->assertResponseStatusCodeSame(404);
    }
}
