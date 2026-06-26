<?php

namespace App\Tests\Entity;

use App\Entity\Competence;
use App\Entity\Cours;
use App\Entity\EleveCompetence;
use PHPUnit\Framework\TestCase;

class CompetenceTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $competence = new Competence();

        $this->assertNull($competence->getId());
        $this->assertNull($competence->getNom());
        $this->assertNull($competence->getNiveau());
        $this->assertNull($competence->getCours());
        $this->assertCount(0, $competence->getEleveCompetences());
    }

    public function testSetNom(): void
    {
        $competence = new Competence();
        $result = $competence->setNom('Algèbre');

        $this->assertSame('Algèbre', $competence->getNom());
        $this->assertSame($competence, $result);
    }

    public function testSetNiveau(): void
    {
        $competence = new Competence();
        $result = $competence->setNiveau('avancé');

        $this->assertSame('avancé', $competence->getNiveau());
        $this->assertSame($competence, $result);
    }

    public function testSetCours(): void
    {
        $competence = new Competence();
        $cours = new Cours();
        $result = $competence->setCours($cours);

        $this->assertSame($cours, $competence->getCours());
        $this->assertSame($competence, $result);
    }

    public function testAddEleveCompetence(): void
    {
        $competence = new Competence();
        $eleveCompetence = new EleveCompetence();

        $result = $competence->addEleveCompetence($eleveCompetence);

        $this->assertCount(1, $competence->getEleveCompetences());
        $this->assertTrue($competence->getEleveCompetences()->contains($eleveCompetence));
        $this->assertSame($competence, $eleveCompetence->getCompetence());
        $this->assertSame($competence, $result);
    }

    public function testAddEleveCompetenceDoesNotDuplicate(): void
    {
        $competence = new Competence();
        $eleveCompetence = new EleveCompetence();

        $competence->addEleveCompetence($eleveCompetence);
        $competence->addEleveCompetence($eleveCompetence);

        $this->assertCount(1, $competence->getEleveCompetences());
    }

    public function testRemoveEleveCompetence(): void
    {
        $competence = new Competence();
        $eleveCompetence = new EleveCompetence();
        $competence->addEleveCompetence($eleveCompetence);

        $result = $competence->removeEleveCompetence($eleveCompetence);

        $this->assertCount(0, $competence->getEleveCompetences());
        $this->assertNull($eleveCompetence->getCompetence());
        $this->assertSame($competence, $result);
    }

    public function testRemoveEleveCompetenceWithDifferentCompetence(): void
    {
        $competence1 = new Competence();
        $competence2 = new Competence();
        $eleveCompetence = new EleveCompetence();

        $competence1->addEleveCompetence($eleveCompetence);
        $eleveCompetence->setCompetence($competence2);

        $competence1->removeEleveCompetence($eleveCompetence);

        $this->assertSame($competence2, $eleveCompetence->getCompetence());
    }
}
