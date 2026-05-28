<?php

namespace App\Tests\Entity;

use App\Entity\Classe;
use App\Entity\Cours;
use App\Entity\Professeur;
use PHPUnit\Framework\TestCase;

class ProfesseurTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $professeur = new Professeur();

        $this->assertCount(0, $professeur->getClasses());
        $this->assertCount(0, $professeur->getCours());
    }

    public function testAddClasse(): void
    {
        $professeur = new Professeur();
        $classe = new Classe();

        $result = $professeur->addClasse($classe);

        $this->assertCount(1, $professeur->getClasses());
        $this->assertTrue($professeur->getClasses()->contains($classe));
        $this->assertSame($professeur, $classe->getProfesseur());
        $this->assertSame($professeur, $result);
    }

    public function testAddClasseDoesNotDuplicate(): void
    {
        $professeur = new Professeur();
        $classe = new Classe();

        $professeur->addClasse($classe);
        $professeur->addClasse($classe);

        $this->assertCount(1, $professeur->getClasses());
    }

    public function testRemoveClasse(): void
    {
        $professeur = new Professeur();
        $classe = new Classe();
        $professeur->addClasse($classe);

        $result = $professeur->removeClasse($classe);

        $this->assertCount(0, $professeur->getClasses());
        $this->assertNull($classe->getProfesseur());
        $this->assertSame($professeur, $result);
    }

    public function testRemoveClasseWithDifferentProfesseur(): void
    {
        $professeur1 = new Professeur();
        $professeur2 = new Professeur();
        $classe = new Classe();

        $professeur1->addClasse($classe);
        $classe->setProfesseur($professeur2);

        $professeur1->removeClasse($classe);

        $this->assertSame($professeur2, $classe->getProfesseur());
    }

    public function testAddCour(): void
    {
        $professeur = new Professeur();
        $cours = new Cours();

        $result = $professeur->addCour($cours);

        $this->assertCount(1, $professeur->getCours());
        $this->assertTrue($professeur->getCours()->contains($cours));
        $this->assertSame($professeur, $cours->getProfesseur());
        $this->assertSame($professeur, $result);
    }

    public function testAddCourDoesNotDuplicate(): void
    {
        $professeur = new Professeur();
        $cours = new Cours();

        $professeur->addCour($cours);
        $professeur->addCour($cours);

        $this->assertCount(1, $professeur->getCours());
    }

    public function testRemoveCour(): void
    {
        $professeur = new Professeur();
        $cours = new Cours();
        $professeur->addCour($cours);

        $result = $professeur->removeCour($cours);

        $this->assertCount(0, $professeur->getCours());
        $this->assertNull($cours->getProfesseur());
        $this->assertSame($professeur, $result);
    }

    public function testRemoveCourWithDifferentProfesseur(): void
    {
        $professeur1 = new Professeur();
        $professeur2 = new Professeur();
        $cours = new Cours();

        $professeur1->addCour($cours);
        $cours->setProfesseur($professeur2);

        $professeur1->removeCour($cours);

        $this->assertSame($professeur2, $cours->getProfesseur());
    }
}
