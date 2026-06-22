<?php

namespace App\Tests\Service;

use App\Entity\Activite;
use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\Progression;
use App\Repository\ActiviteProgressionRepository;
use App\Repository\ProgressionRepository;
use App\Service\ProgressionService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProgressionServiceTest extends TestCase
{
    private ActiviteProgressionRepository&MockObject $activiteProgressionRepository;
    private ProgressionRepository&MockObject $progressionRepository;
    private ProgressionService $service;

    protected function setUp(): void
    {
        $this->activiteProgressionRepository = $this->createMock(ActiviteProgressionRepository::class);
        $this->progressionRepository = $this->createMock(ProgressionRepository::class);

        $this->service = new ProgressionService(
            $this->activiteProgressionRepository,
            $this->progressionRepository,
        );
    }

    public function testDoesNothingWhenActiviteHasNoCours(): void
    {
        $eleve = new Eleve();
        $activite = new Activite();

        $this->progressionRepository->expects($this->never())->method('findOneBy');
        $this->progressionRepository->expects($this->never())->method('save');

        $this->service->updateCourseProgression($eleve, $activite);
    }

    public function testDoesNothingWhenNoProgressionExistsForCourse(): void
    {
        $eleve = new Eleve();
        $cours = new Cours();
        $activite = (new Activite())->setCours($cours);

        $this->progressionRepository->method('findOneBy')->willReturn(null);
        $this->progressionRepository->expects($this->never())->method('save');

        $this->service->updateCourseProgression($eleve, $activite);
    }

    public function testDoesNothingWhenCourseHasNoActivites(): void
    {
        $eleve = new Eleve();
        $cours = $this->createMock(Cours::class);
        $cours->method('getActivites')->willReturn(new ArrayCollection());

        $activite = $this->createMock(Activite::class);
        $activite->method('getCours')->willReturn($cours);

        $progression = new Progression();
        $this->progressionRepository->method('findOneBy')->willReturn($progression);
        $this->progressionRepository->expects($this->never())->method('save');

        $this->service->updateCourseProgression($eleve, $activite);
    }

    public function testCalculatesAndSavesPercentageBasedOnCompletedActivites(): void
    {
        $eleve = new Eleve();
        $cours = $this->createMock(Cours::class);
        $cours->method('getActivites')->willReturn(new ArrayCollection([new Activite(), new Activite(), new Activite(), new Activite()]));

        $activite = $this->createMock(Activite::class);
        $activite->method('getCours')->willReturn($cours);

        $progression = new Progression();
        $progression->setPercentage(0);

        $this->progressionRepository->method('findOneBy')->willReturn($progression);
        $this->activiteProgressionRepository->method('findCompletedActiviteIds')->willReturn([1, 2, 3]);

        $this->progressionRepository->expects($this->once())
            ->method('save')
            ->with($progression, true);

        $this->service->updateCourseProgression($eleve, $activite);

        $this->assertSame(75, $progression->getPercentage());
    }

    public function testCalculatesFullCompletionAs100Percent(): void
    {
        $eleve = new Eleve();
        $cours = $this->createMock(Cours::class);
        $cours->method('getActivites')->willReturn(new ArrayCollection([new Activite(), new Activite()]));

        $activite = $this->createMock(Activite::class);
        $activite->method('getCours')->willReturn($cours);

        $progression = new Progression();

        $this->progressionRepository->method('findOneBy')->willReturn($progression);
        $this->activiteProgressionRepository->method('findCompletedActiviteIds')->willReturn([1, 2]);

        $this->service->updateCourseProgression($eleve, $activite);

        $this->assertSame(100, $progression->getPercentage());
    }

    public function testCalculatesZeroPercentWhenNothingCompleted(): void
    {
        $eleve = new Eleve();
        $cours = $this->createMock(Cours::class);
        $cours->method('getActivites')->willReturn(new ArrayCollection([new Activite(), new Activite()]));

        $activite = $this->createMock(Activite::class);
        $activite->method('getCours')->willReturn($cours);

        $progression = new Progression();
        $progression->setPercentage(50);

        $this->progressionRepository->method('findOneBy')->willReturn($progression);
        $this->activiteProgressionRepository->method('findCompletedActiviteIds')->willReturn([]);

        $this->service->updateCourseProgression($eleve, $activite);

        $this->assertSame(0, $progression->getPercentage());
    }
}
