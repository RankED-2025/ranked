<?php

namespace App\Tests\Entity;

use App\Entity\Cours;
use App\Entity\Difficulte;
use PHPUnit\Framework\TestCase;

class DifficulteTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $difficulte = new Difficulte();

        $this->assertNull($difficulte->getId());
        $this->assertNull($difficulte->getLabel());
        $this->assertCount(0, $difficulte->getCours());
    }

    public function testSetLabel(): void
    {
        $difficulte = new Difficulte();
        $result = $difficulte->setLabel('Difficile');

        $this->assertSame('Difficile', $difficulte->getLabel());
        $this->assertSame($difficulte, $result);
    }

    public function testAddCour(): void
    {
        $difficulte = new Difficulte();
        $cours = new Cours();

        $result = $difficulte->addCour($cours);

        $this->assertCount(1, $difficulte->getCours());
        $this->assertTrue($difficulte->getCours()->contains($cours));
        $this->assertSame($difficulte, $cours->getDifficulte());
        $this->assertSame($difficulte, $result);
    }

    public function testAddCourDoesNotDuplicate(): void
    {
        $difficulte = new Difficulte();
        $cours = new Cours();

        $difficulte->addCour($cours);
        $difficulte->addCour($cours);

        $this->assertCount(1, $difficulte->getCours());
    }

    public function testRemoveCour(): void
    {
        $difficulte = new Difficulte();
        $cours = new Cours();
        $difficulte->addCour($cours);

        $result = $difficulte->removeCour($cours);

        $this->assertCount(0, $difficulte->getCours());
        $this->assertNull($cours->getDifficulte());
        $this->assertSame($difficulte, $result);
    }

    public function testRemoveCourWithDifferentDifficulte(): void
    {
        $difficulte1 = new Difficulte();
        $difficulte2 = new Difficulte();
        $cours = new Cours();

        $difficulte1->addCour($cours);
        $cours->setDifficulte($difficulte2);

        $difficulte1->removeCour($cours);

        $this->assertSame($difficulte2, $cours->getDifficulte());
    }
}
