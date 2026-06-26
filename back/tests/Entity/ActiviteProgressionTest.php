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
}
