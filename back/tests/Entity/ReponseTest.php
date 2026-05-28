<?php

namespace App\Tests\Entity;

use App\Entity\Question;
use App\Entity\Reponse;
use PHPUnit\Framework\TestCase;

class ReponseTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $reponse = new Reponse();

        $this->assertNull($reponse->getId());
        $this->assertNull($reponse->getTexte());
        $this->assertFalse($reponse->isCorrect());
        $this->assertNull($reponse->getQuestion());
    }

    public function testSetTexte(): void
    {
        $reponse = new Reponse();
        $result = $reponse->setTexte('Paris');

        $this->assertSame('Paris', $reponse->getTexte());
        $this->assertSame($reponse, $result);
    }

    public function testSetIsCorrect(): void
    {
        $reponse = new Reponse();
        $result = $reponse->setIsCorrect(true);

        $this->assertTrue($reponse->isCorrect());
        $this->assertSame($reponse, $result);
    }

    public function testSetIsCorrectFalse(): void
    {
        $reponse = new Reponse();
        $reponse->setIsCorrect(true);
        $reponse->setIsCorrect(false);

        $this->assertFalse($reponse->isCorrect());
    }

    public function testSetQuestion(): void
    {
        $reponse = new Reponse();
        $question = new Question();
        $result = $reponse->setQuestion($question);

        $this->assertSame($question, $reponse->getQuestion());
        $this->assertSame($reponse, $result);
    }

    public function testSetQuestionNull(): void
    {
        $reponse = new Reponse();
        $question = new Question();
        $reponse->setQuestion($question);

        $reponse->setQuestion(null);

        $this->assertNull($reponse->getQuestion());
    }
}
