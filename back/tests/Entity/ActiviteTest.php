<?php

namespace App\Tests\Entity;

use App\Entity\Activite;
use App\Entity\Contenu;
use App\Entity\Cours;
use App\Entity\Qcm;
use PHPUnit\Framework\TestCase;

class ActiviteTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $activite = new Activite();

        $this->assertNull($activite->getId());
        $this->assertNull($activite->getType());
        $this->assertNull($activite->getOrdre());
        $this->assertNull($activite->getCours());
        $this->assertNull($activite->getContenu());
        $this->assertNull($activite->getQcm());
    }

    public function testSetType(): void
    {
        $activite = new Activite();
        $result = $activite->setType('qcm');

        $this->assertSame('qcm', $activite->getType());
        $this->assertSame($activite, $result);
    }

    public function testSetOrdre(): void
    {
        $activite = new Activite();
        $result = $activite->setOrdre(3);

        $this->assertSame(3, $activite->getOrdre());
        $this->assertSame($activite, $result);
    }

    public function testSetCours(): void
    {
        $activite = new Activite();
        $cours = new Cours();
        $result = $activite->setCours($cours);

        $this->assertSame($cours, $activite->getCours());
        $this->assertSame($activite, $result);
    }

    public function testSetContenuWithObject(): void
    {
        $activite = new Activite();
        $contenu = new Contenu();
        $activite->setContenu($contenu);

        $this->assertSame($contenu, $activite->getContenu());
        $this->assertSame($activite, $contenu->getActivite());
    }

    public function testSetContenuWithNullWhenContenuExists(): void
    {
        $activite = new Activite();
        $contenu = new Contenu();
        $activite->setContenu($contenu);

        $activite->setContenu(null);

        $this->assertNull($activite->getContenu());
        $this->assertNull($contenu->getActivite());
    }

    public function testSetContenuWithNullWhenNoContenu(): void
    {
        $activite = new Activite();
        $activite->setContenu(null);

        $this->assertNull($activite->getContenu());
    }

    public function testSetQcmWithObject(): void
    {
        $activite = new Activite();
        $qcm = new Qcm();
        $activite->setQcm($qcm);

        $this->assertSame($qcm, $activite->getQcm());
        $this->assertSame($activite, $qcm->getActivite());
    }

    public function testSetQcmWithNullWhenQcmExists(): void
    {
        $activite = new Activite();
        $qcm = new Qcm();
        $activite->setQcm($qcm);

        $activite->setQcm(null);

        $this->assertNull($activite->getQcm());
        $this->assertNull($qcm->getActivite());
    }

    public function testSetQcmWithNullWhenNoQcm(): void
    {
        $activite = new Activite();
        $activite->setQcm(null);

        $this->assertNull($activite->getQcm());
    }
}
