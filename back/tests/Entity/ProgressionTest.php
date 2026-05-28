<?php

namespace App\Tests\Entity;

use App\Entity\Badge;
use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\Progression;
use PHPUnit\Framework\TestCase;

class ProgressionTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $progression = new Progression();

        $this->assertNull($progression->getId());
        $this->assertNull($progression->getEleve());
        $this->assertNull($progression->getCours());
        $this->assertNull($progression->getBadge());
        $this->assertSame(0, $progression->getPercentage());
    }

    public function testSetEleve(): void
    {
        $progression = new Progression();
        $eleve = new Eleve();
        $result = $progression->setEleve($eleve);

        $this->assertSame($eleve, $progression->getEleve());
        $this->assertSame($progression, $result);
    }

    public function testSetCours(): void
    {
        $progression = new Progression();
        $cours = new Cours();
        $result = $progression->setCours($cours);

        $this->assertSame($cours, $progression->getCours());
        $this->assertSame($progression, $result);
    }

    public function testSetBadge(): void
    {
        $progression = new Progression();
        $badge = new Badge();
        $result = $progression->setBadge($badge);

        $this->assertSame($badge, $progression->getBadge());
        $this->assertSame($progression, $result);
    }

    public function testSetPercentageValidValue(): void
    {
        $progression = new Progression();
        $result = $progression->setPercentage(50);

        $this->assertSame(50, $progression->getPercentage());
        $this->assertSame($progression, $result);
    }

    public function testSetPercentageBelowZeroClampsToZero(): void
    {
        $progression = new Progression();
        $progression->setPercentage(-10);

        $this->assertSame(0, $progression->getPercentage());
    }

    public function testSetPercentageAbove100ClampsTo100(): void
    {
        $progression = new Progression();
        $progression->setPercentage(150);

        $this->assertSame(100, $progression->getPercentage());
    }

    public function testSetPercentageAtBoundaries(): void
    {
        $progression = new Progression();

        $progression->setPercentage(0);
        $this->assertSame(0, $progression->getPercentage());

        $progression->setPercentage(100);
        $this->assertSame(100, $progression->getPercentage());
    }
}
