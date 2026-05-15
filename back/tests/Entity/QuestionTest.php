<?php

namespace App\Tests\Entity;

use App\Entity\Qcm;
use App\Entity\Question;
use App\Entity\Reponse;
use PHPUnit\Framework\TestCase;

class QuestionTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $question = new Question();

        $this->assertNull($question->getId());
        $this->assertNull($question->getEnonce());
        $this->assertNull($question->getQcm());
        $this->assertCount(0, $question->getReponses());
    }

    public function testSetEnonce(): void
    {
        $question = new Question();
        $result = $question->setEnonce('Quelle est la capitale de la France ?');

        $this->assertSame('Quelle est la capitale de la France ?', $question->getEnonce());
        $this->assertSame($question, $result);
    }

    public function testSetQcm(): void
    {
        $question = new Question();
        $qcm = new Qcm();
        $result = $question->setQcm($qcm);

        $this->assertSame($qcm, $question->getQcm());
        $this->assertSame($question, $result);
    }

    public function testSetQcmNull(): void
    {
        $question = new Question();
        $qcm = new Qcm();
        $question->setQcm($qcm);

        $question->setQcm(null);

        $this->assertNull($question->getQcm());
    }

    public function testAddReponse(): void
    {
        $question = new Question();
        $reponse = new Reponse();

        $result = $question->addReponse($reponse);

        $this->assertCount(1, $question->getReponses());
        $this->assertTrue($question->getReponses()->contains($reponse));
        $this->assertSame($question, $reponse->getQuestion());
        $this->assertSame($question, $result);
    }

    public function testAddReponseDoesNotDuplicate(): void
    {
        $question = new Question();
        $reponse = new Reponse();

        $question->addReponse($reponse);
        $question->addReponse($reponse);

        $this->assertCount(1, $question->getReponses());
    }

    public function testRemoveReponse(): void
    {
        $question = new Question();
        $reponse = new Reponse();
        $question->addReponse($reponse);

        $result = $question->removeReponse($reponse);

        $this->assertCount(0, $question->getReponses());
        $this->assertNull($reponse->getQuestion());
        $this->assertSame($question, $result);
    }

    public function testRemoveReponseWithDifferentQuestion(): void
    {
        $question1 = new Question();
        $question2 = new Question();
        $reponse = new Reponse();

        $question1->addReponse($reponse);
        $reponse->setQuestion($question2);

        $question1->removeReponse($reponse);

        $this->assertSame($question2, $reponse->getQuestion());
    }
}
