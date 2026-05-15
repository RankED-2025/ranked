<?php

namespace App\Tests\Entity;

use App\Entity\Badge;
use App\Entity\Progression;
use PHPUnit\Framework\TestCase;

class BadgeTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $badge = new Badge();

        $this->assertNull($badge->getId());
        $this->assertNull($badge->getType());
        $this->assertNull($badge->getLabel());
        $this->assertCount(0, $badge->getProgressions());
    }

    public function testSetType(): void
    {
        $badge = new Badge();
        $result = $badge->setType('gold');

        $this->assertSame('gold', $badge->getType());
        $this->assertSame($badge, $result);
    }

    public function testSetLabel(): void
    {
        $badge = new Badge();
        $result = $badge->setLabel('Gold Badge');

        $this->assertSame('Gold Badge', $badge->getLabel());
        $this->assertSame($badge, $result);
    }

    public function testAddProgression(): void
    {
        $badge = new Badge();
        $progression = new Progression();

        $result = $badge->addProgression($progression);

        $this->assertCount(1, $badge->getProgressions());
        $this->assertTrue($badge->getProgressions()->contains($progression));
        $this->assertSame($badge, $progression->getBadge());
        $this->assertSame($badge, $result);
    }

    public function testAddProgressionDoesNotDuplicate(): void
    {
        $badge = new Badge();
        $progression = new Progression();

        $badge->addProgression($progression);
        $badge->addProgression($progression);

        $this->assertCount(1, $badge->getProgressions());
    }

    public function testRemoveProgression(): void
    {
        $badge = new Badge();
        $progression = new Progression();
        $badge->addProgression($progression);

        $result = $badge->removeProgression($progression);

        $this->assertCount(0, $badge->getProgressions());
        $this->assertNull($progression->getBadge());
        $this->assertSame($badge, $result);
    }

    public function testRemoveProgressionWithDifferentBadge(): void
    {
        $badge1 = new Badge();
        $badge2 = new Badge();
        $progression = new Progression();

        $badge1->addProgression($progression);
        $progression->setBadge($badge2);

        $badge1->removeProgression($progression);

        $this->assertSame($badge2, $progression->getBadge());
    }
}
