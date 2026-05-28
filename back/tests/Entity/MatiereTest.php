<?php

namespace App\Tests\Entity;

use App\Entity\Cours;
use App\Entity\Matiere;
use PHPUnit\Framework\TestCase;

class MatiereTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $matiere = new Matiere();

        $this->assertNull($matiere->getId());
        $this->assertNull($matiere->getLibelle());
        $this->assertCount(0, $matiere->getCours());
    }

    public function testSetLibelle(): void
    {
        $matiere = new Matiere();
        $result = $matiere->setLibelle('Mathématiques');

        $this->assertSame('Mathématiques', $matiere->getLibelle());
        $this->assertSame($matiere, $result);
    }

    public function testAddCour(): void
    {
        $matiere = new Matiere();
        $cours = new Cours();

        $result = $matiere->addCour($cours);

        $this->assertCount(1, $matiere->getCours());
        $this->assertTrue($matiere->getCours()->contains($cours));
        $this->assertSame($matiere, $cours->getMatiere());
        $this->assertSame($matiere, $result);
    }

    public function testAddCourDoesNotDuplicate(): void
    {
        $matiere = new Matiere();
        $cours = new Cours();

        $matiere->addCour($cours);
        $matiere->addCour($cours);

        $this->assertCount(1, $matiere->getCours());
    }

    public function testRemoveCour(): void
    {
        $matiere = new Matiere();
        $cours = new Cours();
        $matiere->addCour($cours);

        $result = $matiere->removeCour($cours);

        $this->assertCount(0, $matiere->getCours());
        $this->assertNull($cours->getMatiere());
        $this->assertSame($matiere, $result);
    }

    public function testRemoveCourWithDifferentMatiere(): void
    {
        $matiere1 = new Matiere();
        $matiere2 = new Matiere();
        $cours = new Cours();

        $matiere1->addCour($cours);
        $cours->setMatiere($matiere2);

        $matiere1->removeCour($cours);

        $this->assertSame($matiere2, $cours->getMatiere());
    }
}
