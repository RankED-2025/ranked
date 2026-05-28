<?php

namespace App\Tests\Entity;

use App\Entity\Activite;
use App\Entity\Qcm;
use App\Entity\Question;
use PHPUnit\Framework\TestCase;

class QcmTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $qcm = new Qcm();

        $this->assertNull($qcm->getId());
        $this->assertNull($qcm->getGainPts());
        $this->assertNull($qcm->getActivite());
        $this->assertCount(0, $qcm->getQuestions());
    }

    public function testSetGainPts(): void
    {
        $qcm = new Qcm();
        $result = $qcm->setGainPts(10);

        $this->assertSame(10, $qcm->getGainPts());
        $this->assertSame($qcm, $result);
    }

    public function testSetActivite(): void
    {
        $qcm = new Qcm();
        $activite = new Activite();
        $result = $qcm->setActivite($activite);

        $this->assertSame($activite, $qcm->getActivite());
        $this->assertSame($qcm, $result);
    }

    public function testSetActiviteNull(): void
    {
        $qcm = new Qcm();
        $activite = new Activite();
        $qcm->setActivite($activite);

        $qcm->setActivite(null);

        $this->assertNull($qcm->getActivite());
    }

    public function testAddQuestion(): void
    {
        $qcm = new Qcm();
        $question = new Question();

        $result = $qcm->addQuestion($question);

        $this->assertCount(1, $qcm->getQuestions());
        $this->assertTrue($qcm->getQuestions()->contains($question));
        $this->assertSame($qcm, $question->getQcm());
        $this->assertSame($qcm, $result);
    }

    public function testAddQuestionDoesNotDuplicate(): void
    {
        $qcm = new Qcm();
        $question = new Question();

        $qcm->addQuestion($question);
        $qcm->addQuestion($question);

        $this->assertCount(1, $qcm->getQuestions());
    }

    public function testRemoveQuestion(): void
    {
        $qcm = new Qcm();
        $question = new Question();
        $qcm->addQuestion($question);

        $result = $qcm->removeQuestion($question);

        $this->assertCount(0, $qcm->getQuestions());
        $this->assertNull($question->getQcm());
        $this->assertSame($qcm, $result);
    }

    public function testRemoveQuestionWithDifferentQcm(): void
    {
        $qcm1 = new Qcm();
        $qcm2 = new Qcm();
        $question = new Question();

        $qcm1->addQuestion($question);
        $question->setQcm($qcm2);

        $qcm1->removeQuestion($question);

        $this->assertSame($qcm2, $question->getQcm());
    }
}
