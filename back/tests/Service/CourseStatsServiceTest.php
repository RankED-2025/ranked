<?php

namespace App\Tests\Service;

use App\Entity\Cours;
use App\Entity\Progression;
use App\Service\CourseStatsService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CourseStatsServiceTest extends TestCase
{
    private CourseStatsService $service;

    protected function setUp(): void
    {
        $this->service = new CourseStatsService();
    }

    public function testCalculateAverageProgressionWithNoProgressions(): void
    {
        $cours = $this->createMock(Cours::class);
        $cours->method('getProgressions')->willReturn(new ArrayCollection());

        $result = $this->service->calculateAverageProgression($cours);

        $this->assertSame(0, $result);
    }

    public function testCalculateAverageProgressionWithSingleProgression(): void
    {
        $progression = new Progression();
        $progression->setPercentage(60);

        $cours = $this->createMock(Cours::class);
        $cours->method('getProgressions')->willReturn(new ArrayCollection([$progression]));

        $result = $this->service->calculateAverageProgression($cours);

        $this->assertSame(60.0, (float) $result);
    }

    public function testCalculateAverageProgressionWithMultipleProgressions(): void
    {
        $p1 = new Progression();
        $p1->setPercentage(40);

        $p2 = new Progression();
        $p2->setPercentage(80);

        $cours = $this->createMock(Cours::class);
        $cours->method('getProgressions')->willReturn(new ArrayCollection([$p1, $p2]));

        $result = $this->service->calculateAverageProgression($cours);

        $this->assertSame(60.0, (float) $result);
    }
}
