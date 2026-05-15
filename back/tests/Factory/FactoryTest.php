<?php

namespace App\Tests\Factory;

use App\Factory\ActiviteFactory;
use App\Factory\BadgeFactory;
use App\Factory\ContenuFactory;
use App\Factory\DifficulteFactory;
use App\Factory\MatiereFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class FactoryTest extends WebTestCase
{
    use ResetDatabase;

    public function testBadgeCreateFromBase(): void
    {
        $badges = BadgeFactory::createFromBase();

        $this->assertCount(5, $badges);
        $this->assertSame('Débutant', $badges[0]->getLabel());
        $this->assertSame('bronze', $badges[0]->getType());
    }

    public function testBadgeCreateFromBaseWithOverrides(): void
    {
        $badges = BadgeFactory::createFromBase(['label' => 'Custom']);

        $this->assertCount(5, $badges);
        foreach ($badges as $badge) {
            $this->assertSame('Custom', $badge->getLabel());
        }
    }

    public function testDifficulteCreateFromBase(): void
    {
        $difficultes = DifficulteFactory::createFromBase();

        $this->assertCount(4, $difficultes);
        $this->assertSame('Facile', $difficultes[0]->getLabel());
    }

    public function testDifficulteCreateFromBaseWithOverrides(): void
    {
        $difficultes = DifficulteFactory::createFromBase();

        $this->assertNotEmpty($difficultes);
    }

    public function testMatiereCreateFromBase(): void
    {
        $matieres = MatiereFactory::createFromBase();

        $this->assertCount(8, $matieres);
        $this->assertSame('Mathématiques', $matieres[0]->getLibelle());
    }

    public function testMatiereCreateFromBaseWithOverrides(): void
    {
        $matieres = MatiereFactory::createFromBase();

        $this->assertNotEmpty($matieres);
    }

    public function testContenuFactoryCreatesContenu(): void
    {
        $activite = ActiviteFactory::createOne(['type' => 'contenu']);
        $contenu = ContenuFactory::createOne(['activite' => $activite]);

        $this->assertNotNull($contenu->getType());
        $this->assertNotNull($contenu->getUrl());
        $this->assertSame($activite->_real(), $contenu->getActivite());
    }

    public function testActiviteFactoryWithContenu(): void
    {
        $activite = ActiviteFactory::new()->withContenu()->create();

        $this->assertSame('contenu', $activite->getType());
        $this->assertNotNull($activite->getContenu());
        $this->assertNull($activite->getQcm());
    }

    public function testActiviteFactoryWithQcm(): void
    {
        $activite = ActiviteFactory::new()->withQcm()->create();

        $this->assertSame('qcm', $activite->getType());
        $this->assertNotNull($activite->getQcm());
        $this->assertNull($activite->getContenu());
    }
}
