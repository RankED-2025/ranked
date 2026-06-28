<?php

namespace App\Tests\Entity;

use App\Entity\Activite;
use App\Entity\ActiviteProgression;
use App\Entity\Eleve;
use PHPUnit\Framework\TestCase;

class ActiviteProgressionTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $activiteProgression = new ActiviteProgression();

        $this->assertNull($activiteProgression->getId());
        $this->assertNull($activiteProgression->getEleve());
        $this->assertNull($activiteProgression->getActivite());
        $this->assertNull($activiteProgression->getCompletedAt());
        $this->assertNull($activiteProgression->getScore());
        $this->assertNull($activiteProgression->getTotal());
        $this->assertNull($activiteProgression->getEarnedPts());
    }

    public function testSetEleve(): void
    {
        $activiteProgression = new ActiviteProgression();
        $eleve = new Eleve();
        $result = $activiteProgression->setEleve($eleve);

        $this->assertSame($eleve, $activiteProgression->getEleve());
        $this->assertSame($activiteProgression, $result);
    }

    public function testSetActivite(): void
    {
        $activiteProgression = new ActiviteProgression();
        $activite = new Activite();
        $result = $activiteProgression->setActivite($activite);

        $this->assertSame($activite, $activiteProgression->getActivite());
        $this->assertSame($activiteProgression, $result);
    }

    public function testSetCompletedAt(): void
    {
        $activiteProgression = new ActiviteProgression();
        $completedAt = new \DateTimeImmutable('2026-01-01');
        $result = $activiteProgression->setCompletedAt($completedAt);

        $this->assertSame($completedAt, $activiteProgression->getCompletedAt());
        $this->assertSame($activiteProgression, $result);
    }

    public function testSetCompletedAtNull(): void
    {
        $activiteProgression = new ActiviteProgression();
        $activiteProgression->setCompletedAt(new \DateTimeImmutable());

        $activiteProgression->setCompletedAt(null);

        $this->assertNull($activiteProgression->getCompletedAt());
    }

    public function testSetScore(): void
    {
        $activiteProgression = new ActiviteProgression();
        $result = $activiteProgression->setScore(3);

        $this->assertSame(3, $activiteProgression->getScore());
        $this->assertSame($activiteProgression, $result);

        $activiteProgression->setScore(null);
        $this->assertNull($activiteProgression->getScore());
    }

    public function testSetTotal(): void
    {
        $activiteProgression = new ActiviteProgression();
        $result = $activiteProgression->setTotal(5);

        $this->assertSame(5, $activiteProgression->getTotal());
        $this->assertSame($activiteProgression, $result);

        $activiteProgression->setTotal(null);
        $this->assertNull($activiteProgression->getTotal());
    }

    public function testSetEarnedPts(): void
    {
        $activiteProgression = new ActiviteProgression();
        $result = $activiteProgression->setEarnedPts(12);

        $this->assertSame(12, $activiteProgression->getEarnedPts());
        $this->assertSame($activiteProgression, $result);

        $activiteProgression->setEarnedPts(null);
        $this->assertNull($activiteProgression->getEarnedPts());
    }
}
