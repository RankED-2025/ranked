<?php

namespace App\Tests\Entity;

use App\Entity\Classe;
use App\Entity\Eleve;
use App\Entity\Professeur;
use PHPUnit\Framework\TestCase;

class ClasseTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $classe = new Classe();

        $this->assertNull($classe->getId());
        $this->assertNull($classe->getNom());
        $this->assertNull($classe->getProfesseur());
        $this->assertCount(0, $classe->getEleves());
    }

    public function testSetNom(): void
    {
        $classe = new Classe();
        $result = $classe->setNom('6ème A');

        $this->assertSame('6ème A', $classe->getNom());
        $this->assertSame($classe, $result);
    }

    public function testSetProfesseur(): void
    {
        $classe = new Classe();
        $professeur = new Professeur();
        $result = $classe->setProfesseur($professeur);

        $this->assertSame($professeur, $classe->getProfesseur());
        $this->assertSame($classe, $result);
    }

    public function testAddEleve(): void
    {
        $classe = new Classe();
        $eleve = new Eleve();

        $result = $classe->addEleve($eleve);

        $this->assertCount(1, $classe->getEleves());
        $this->assertTrue($classe->getEleves()->contains($eleve));
        $this->assertSame($classe, $eleve->getClasse());
        $this->assertSame($classe, $result);
    }

    public function testAddEleveDoesNotDuplicate(): void
    {
        $classe = new Classe();
        $eleve = new Eleve();

        $classe->addEleve($eleve);
        $classe->addEleve($eleve);

        $this->assertCount(1, $classe->getEleves());
    }

    public function testRemoveEleve(): void
    {
        $classe = new Classe();
        $eleve = new Eleve();
        $classe->addEleve($eleve);

        $result = $classe->removeEleve($eleve);

        $this->assertCount(0, $classe->getEleves());
        $this->assertNull($eleve->getClasse());
        $this->assertSame($classe, $result);
    }

    public function testRemoveEleveWithDifferentClasse(): void
    {
        $classe1 = new Classe();
        $classe2 = new Classe();
        $eleve = new Eleve();

        $classe1->addEleve($eleve);
        $eleve->setClasse($classe2);

        $classe1->removeEleve($eleve);

        $this->assertSame($classe2, $eleve->getClasse());
    }
}
