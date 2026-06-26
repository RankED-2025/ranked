<?php

namespace App\Tests\Entity;

use App\Entity\Competence;
use App\Entity\Eleve;
use App\Entity\EleveCompetence;
use PHPUnit\Framework\TestCase;

class EleveCompetenceTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $eleveCompetence = new EleveCompetence();

        $this->assertNull($eleveCompetence->getId());
        $this->assertNull($eleveCompetence->getEleve());
        $this->assertNull($eleveCompetence->getCompetence());
    }

    public function testSetEleve(): void
    {
        $eleveCompetence = new EleveCompetence();
        $eleve = new Eleve();
        $result = $eleveCompetence->setEleve($eleve);

        $this->assertSame($eleve, $eleveCompetence->getEleve());
        $this->assertSame($eleveCompetence, $result);
    }

    public function testSetEleveNull(): void
    {
        $eleveCompetence = new EleveCompetence();
        $eleve = new Eleve();
        $eleveCompetence->setEleve($eleve);

        $eleveCompetence->setEleve(null);

        $this->assertNull($eleveCompetence->getEleve());
    }

    public function testSetCompetence(): void
    {
        $eleveCompetence = new EleveCompetence();
        $competence = new Competence();
        $result = $eleveCompetence->setCompetence($competence);

        $this->assertSame($competence, $eleveCompetence->getCompetence());
        $this->assertSame($eleveCompetence, $result);
    }

    public function testSetCompetenceNull(): void
    {
        $eleveCompetence = new EleveCompetence();
        $competence = new Competence();
        $eleveCompetence->setCompetence($competence);

        $eleveCompetence->setCompetence(null);

        $this->assertNull($eleveCompetence->getCompetence());
    }
}
