<?php

namespace App\Tests\Entity;

use App\Entity\Activite;
use App\Entity\Contenu;
use PHPUnit\Framework\TestCase;

class ContenuTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $contenu = new Contenu();

        $this->assertNull($contenu->getId());
        $this->assertNull($contenu->getType());
        $this->assertNull($contenu->getUrl());
        $this->assertNull($contenu->getActivite());
    }

    public function testSetType(): void
    {
        $contenu = new Contenu();
        $result = $contenu->setType('video');

        $this->assertSame('video', $contenu->getType());
        $this->assertSame($contenu, $result);
    }

    public function testSetUrl(): void
    {
        $contenu = new Contenu();
        $result = $contenu->setUrl('https://example.com/video.mp4');

        $this->assertSame('https://example.com/video.mp4', $contenu->getUrl());
        $this->assertSame($contenu, $result);
    }

    public function testSetActivite(): void
    {
        $contenu = new Contenu();
        $activite = new Activite();
        $result = $contenu->setActivite($activite);

        $this->assertSame($activite, $contenu->getActivite());
        $this->assertSame($contenu, $result);
    }

    public function testSetActiviteNull(): void
    {
        $contenu = new Contenu();
        $activite = new Activite();
        $contenu->setActivite($activite);

        $contenu->setActivite(null);

        $this->assertNull($contenu->getActivite());
    }
}
