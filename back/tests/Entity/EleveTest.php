<?php

namespace App\Tests\Entity;

use App\Entity\Classe;
use App\Entity\Eleve;
use App\Entity\EleveCompetence;
use App\Entity\Progression;
use PHPUnit\Framework\TestCase;

class EleveTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $eleve = new Eleve();

        $this->assertNull($eleve->getClasse());
        $this->assertCount(0, $eleve->getProgressions());
        $this->assertCount(0, $eleve->getEleveCompetences());
    }

    public function testSetClasse(): void
    {
        $eleve = new Eleve();
        $classe = new Classe();
        $result = $eleve->setClasse($classe);

        $this->assertSame($classe, $eleve->getClasse());
        $this->assertSame($eleve, $result);
    }

    public function testSetClasseNull(): void
    {
        $eleve = new Eleve();
        $classe = new Classe();
        $eleve->setClasse($classe);

        $eleve->setClasse(null);

        $this->assertNull($eleve->getClasse());
    }

    public function testAddProgression(): void
    {
        $eleve = new Eleve();
        $progression = new Progression();

        $result = $eleve->addProgression($progression);

        $this->assertCount(1, $eleve->getProgressions());
        $this->assertTrue($eleve->getProgressions()->contains($progression));
        $this->assertSame($eleve, $progression->getEleve());
        $this->assertSame($eleve, $result);
    }

    public function testAddProgressionDoesNotDuplicate(): void
    {
        $eleve = new Eleve();
        $progression = new Progression();

        $eleve->addProgression($progression);
        $eleve->addProgression($progression);

        $this->assertCount(1, $eleve->getProgressions());
    }

    public function testRemoveProgression(): void
    {
        $eleve = new Eleve();
        $progression = new Progression();
        $eleve->addProgression($progression);

        $result = $eleve->removeProgression($progression);

        $this->assertCount(0, $eleve->getProgressions());
        $this->assertNull($progression->getEleve());
        $this->assertSame($eleve, $result);
    }

    public function testRemoveProgressionWithDifferentEleve(): void
    {
        $eleve1 = new Eleve();
        $eleve2 = new Eleve();
        $progression = new Progression();

        $eleve1->addProgression($progression);
        $progression->setEleve($eleve2);

        $eleve1->removeProgression($progression);

        $this->assertSame($eleve2, $progression->getEleve());
    }

    public function testAddEleveCompetence(): void
    {
        $eleve = new Eleve();
        $eleveCompetence = new EleveCompetence();

        $result = $eleve->addEleveCompetence($eleveCompetence);

        $this->assertCount(1, $eleve->getEleveCompetences());
        $this->assertTrue($eleve->getEleveCompetences()->contains($eleveCompetence));
        $this->assertSame($eleve, $eleveCompetence->getEleve());
        $this->assertSame($eleve, $result);
    }

    public function testAddEleveCompetenceDoesNotDuplicate(): void
    {
        $eleve = new Eleve();
        $eleveCompetence = new EleveCompetence();

        $eleve->addEleveCompetence($eleveCompetence);
        $eleve->addEleveCompetence($eleveCompetence);

        $this->assertCount(1, $eleve->getEleveCompetences());
    }

    public function testRemoveEleveCompetence(): void
    {
        $eleve = new Eleve();
        $eleveCompetence = new EleveCompetence();
        $eleve->addEleveCompetence($eleveCompetence);

        $result = $eleve->removeEleveCompetence($eleveCompetence);

        $this->assertCount(0, $eleve->getEleveCompetences());
        $this->assertNull($eleveCompetence->getEleve());
        $this->assertSame($eleve, $result);
    }

    public function testRemoveEleveCompetenceWithDifferentEleve(): void
    {
        $eleve1 = new Eleve();
        $eleve2 = new Eleve();
        $eleveCompetence = new EleveCompetence();

        $eleve1->addEleveCompetence($eleveCompetence);
        $eleveCompetence->setEleve($eleve2);

        $eleve1->removeEleveCompetence($eleveCompetence);

        $this->assertSame($eleve2, $eleveCompetence->getEleve());
    }
}
