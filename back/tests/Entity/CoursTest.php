<?php

namespace App\Tests\Entity;

use App\Entity\Activite;
use App\Entity\Competence;
use App\Entity\Cours;
use App\Entity\Difficulte;
use App\Entity\Matiere;
use App\Entity\Professeur;
use App\Entity\Progression;
use PHPUnit\Framework\TestCase;

class CoursTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $cours = new Cours();

        $this->assertNull($cours->getId());
        $this->assertNull($cours->getProfesseur());
        $this->assertNull($cours->getMatiere());
        $this->assertNull($cours->getDifficulte());
        $this->assertNull($cours->getTitre());
        $this->assertNull($cours->getDescription());
        $this->assertCount(0, $cours->getActivites());
        $this->assertCount(0, $cours->getCompetences());
        $this->assertCount(0, $cours->getProgressions());
    }

    public function testSetProfesseur(): void
    {
        $cours = new Cours();
        $professeur = new Professeur();
        $result = $cours->setProfesseur($professeur);

        $this->assertSame($professeur, $cours->getProfesseur());
        $this->assertSame($cours, $result);
    }

    public function testSetMatiere(): void
    {
        $cours = new Cours();
        $matiere = new Matiere();
        $result = $cours->setMatiere($matiere);

        $this->assertSame($matiere, $cours->getMatiere());
        $this->assertSame($cours, $result);
    }

    public function testSetDifficulte(): void
    {
        $cours = new Cours();
        $difficulte = new Difficulte();
        $result = $cours->setDifficulte($difficulte);

        $this->assertSame($difficulte, $cours->getDifficulte());
        $this->assertSame($cours, $result);
    }

    public function testSetTitre(): void
    {
        $cours = new Cours();
        $result = $cours->setTitre('Introduction to PHP');

        $this->assertSame('Introduction to PHP', $cours->getTitre());
        $this->assertSame($cours, $result);
    }

    public function testSetDescription(): void
    {
        $cours = new Cours();
        $result = $cours->setDescription('A comprehensive intro.');

        $this->assertSame('A comprehensive intro.', $cours->getDescription());
        $this->assertSame($cours, $result);
    }

    public function testAddActivite(): void
    {
        $cours = new Cours();
        $activite = new Activite();

        $result = $cours->addActivite($activite);

        $this->assertCount(1, $cours->getActivites());
        $this->assertTrue($cours->getActivites()->contains($activite));
        $this->assertSame($cours, $activite->getCours());
        $this->assertSame($cours, $result);
    }

    public function testAddActiviteDoesNotDuplicate(): void
    {
        $cours = new Cours();
        $activite = new Activite();

        $cours->addActivite($activite);
        $cours->addActivite($activite);

        $this->assertCount(1, $cours->getActivites());
    }

    public function testRemoveActivite(): void
    {
        $cours = new Cours();
        $activite = new Activite();
        $cours->addActivite($activite);

        $result = $cours->removeActivite($activite);

        $this->assertCount(0, $cours->getActivites());
        $this->assertNull($activite->getCours());
        $this->assertSame($cours, $result);
    }

    public function testRemoveActiviteWithDifferentCours(): void
    {
        $cours1 = new Cours();
        $cours2 = new Cours();
        $activite = new Activite();

        $cours1->addActivite($activite);
        $activite->setCours($cours2);

        $cours1->removeActivite($activite);

        $this->assertSame($cours2, $activite->getCours());
    }

    public function testAddCompetence(): void
    {
        $cours = new Cours();
        $competence = new Competence();

        $result = $cours->addCompetence($competence);

        $this->assertCount(1, $cours->getCompetences());
        $this->assertTrue($cours->getCompetences()->contains($competence));
        $this->assertSame($cours, $competence->getCours());
        $this->assertSame($cours, $result);
    }

    public function testAddCompetenceDoesNotDuplicate(): void
    {
        $cours = new Cours();
        $competence = new Competence();

        $cours->addCompetence($competence);
        $cours->addCompetence($competence);

        $this->assertCount(1, $cours->getCompetences());
    }

    public function testRemoveCompetence(): void
    {
        $cours = new Cours();
        $competence = new Competence();
        $cours->addCompetence($competence);

        $result = $cours->removeCompetence($competence);

        $this->assertCount(0, $cours->getCompetences());
        $this->assertNull($competence->getCours());
        $this->assertSame($cours, $result);
    }

    public function testRemoveCompetenceWithDifferentCours(): void
    {
        $cours1 = new Cours();
        $cours2 = new Cours();
        $competence = new Competence();

        $cours1->addCompetence($competence);
        $competence->setCours($cours2);

        $cours1->removeCompetence($competence);

        $this->assertSame($cours2, $competence->getCours());
    }

    public function testAddProgression(): void
    {
        $cours = new Cours();
        $progression = new Progression();

        $result = $cours->addProgression($progression);

        $this->assertCount(1, $cours->getProgressions());
        $this->assertTrue($cours->getProgressions()->contains($progression));
        $this->assertSame($cours, $progression->getCours());
        $this->assertSame($cours, $result);
    }

    public function testAddProgressionDoesNotDuplicate(): void
    {
        $cours = new Cours();
        $progression = new Progression();

        $cours->addProgression($progression);
        $cours->addProgression($progression);

        $this->assertCount(1, $cours->getProgressions());
    }

    public function testRemoveProgression(): void
    {
        $cours = new Cours();
        $progression = new Progression();
        $cours->addProgression($progression);

        $result = $cours->removeProgression($progression);

        $this->assertCount(0, $cours->getProgressions());
        $this->assertNull($progression->getCours());
        $this->assertSame($cours, $result);
    }

    public function testRemoveProgressionWithDifferentCours(): void
    {
        $cours1 = new Cours();
        $cours2 = new Cours();
        $progression = new Progression();

        $cours1->addProgression($progression);
        $progression->setCours($cours2);

        $cours1->removeProgression($progression);

        $this->assertSame($cours2, $progression->getCours());
    }
}
