<?php

namespace App\Tests\Service;

use App\Entity\Activite;
use App\Entity\Badge;
use App\Entity\Contenu;
use App\Entity\Cours;
use App\Entity\Difficulte;
use App\Entity\Matiere;
use App\Entity\Professeur;
use App\Entity\Progression;
use App\Entity\Qcm;
use App\Service\CourseMapperService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CourseMapperServiceTest extends TestCase
{
    private CourseMapperService $service;

    protected function setUp(): void
    {
        $this->service = new CourseMapperService();
    }

    public function testMapToDefaultFormatWithMinimalData(): void
    {
        $cours = $this->createMock(Cours::class);
        $cours->method('getId')->willReturn(1);
        $cours->method('getProfesseur')->willReturn(null);
        $cours->method('getTitre')->willReturn('Test');
        $cours->method('getDescription')->willReturn(null);
        $cours->method('getMatiere')->willReturn(null);
        $cours->method('getDifficulte')->willReturn(null);

        $result = $this->service->mapToDefaultFormat($cours);

        $this->assertArrayHasKey('cours', $result);
        $this->assertArrayHasKey('pourcentage', $result);
        $this->assertArrayHasKey('badge', $result);
        $this->assertNull($result['pourcentage']);
        $this->assertNull($result['badge']);
        $this->assertNull($result['cours']['matiere']);
        $this->assertNull($result['cours']['difficulte']);
    }

    public function testMapToDefaultFormatWithProgression(): void
    {
        $cours = $this->createMock(Cours::class);
        $cours->method('getId')->willReturn(1);
        $cours->method('getProfesseur')->willReturn(null);
        $cours->method('getTitre')->willReturn('Test');
        $cours->method('getDescription')->willReturn(null);
        $cours->method('getMatiere')->willReturn(null);
        $cours->method('getDifficulte')->willReturn(null);

        $progression = new Progression();
        $progression->setPercentage(75);

        $result = $this->service->mapToDefaultFormat($cours, $progression);

        $this->assertSame(75, $result['pourcentage']);
    }

    public function testMapToDefaultFormatWithBadge(): void
    {
        $cours = $this->createMock(Cours::class);
        $cours->method('getId')->willReturn(1);
        $cours->method('getProfesseur')->willReturn(null);
        $cours->method('getTitre')->willReturn('Test');
        $cours->method('getDescription')->willReturn(null);
        $cours->method('getMatiere')->willReturn(null);
        $cours->method('getDifficulte')->willReturn(null);

        $badge = new Badge();
        $badge->setType('gold');
        $badge->setLabel('Or');

        $result = $this->service->mapToDefaultFormat($cours, null, $badge);

        $this->assertNotNull($result['badge']);
        $this->assertSame('gold', $result['badge']['type']);
        $this->assertSame('Or', $result['badge']['label']);
    }

    public function testMapToDefaultContentFormatWithNoActivites(): void
    {
        $cours = $this->createMock(Cours::class);
        $cours->method('getActivites')->willReturn(new ArrayCollection());

        $result = $this->service->mapToDefaultContentFormat($cours);

        $this->assertSame([], $result);
    }

    public function testMapToDefaultContentFormatWithContenuActivite(): void
    {
        $contenu = new Contenu();
        $contenu->setType('video');
        $contenu->setUrl('https://example.com/video.mp4');

        $activite = new Activite();
        $activite->setType('contenu');
        $activite->setOrdre(1);
        $activite->setContenu($contenu);

        $cours = $this->createMock(Cours::class);
        $cours->method('getActivites')->willReturn(new ArrayCollection([$activite]));

        $result = $this->service->mapToDefaultContentFormat($cours);

        $this->assertCount(1, $result);
        $this->assertSame('contenu', $result[0]['type']);
        $this->assertSame(1, $result[0]['ordre']);
        $this->assertNotNull($result[0]['contenu']);
        $this->assertSame('video', $result[0]['contenu']['type']);
        $this->assertNull($result[0]['qcm']);
    }

    public function testMapToDefaultContentFormatWithQcmActivite(): void
    {
        $qcm = new Qcm();
        $qcm->setGainPts(20);

        $activite = new Activite();
        $activite->setType('qcm');
        $activite->setOrdre(2);
        $activite->setQcm($qcm);

        $cours = $this->createMock(Cours::class);
        $cours->method('getActivites')->willReturn(new ArrayCollection([$activite]));

        $result = $this->service->mapToDefaultContentFormat($cours);

        $this->assertCount(1, $result);
        $this->assertSame('qcm', $result[0]['type']);
        $this->assertNotNull($result[0]['qcm']);
        $this->assertSame(20, $result[0]['qcm']['gainPts']);
        $this->assertNull($result[0]['contenu']);
    }

    public function testMapToDefaultContentFormatSortsByOrdre(): void
    {
        $activite1 = new Activite();
        $activite1->setType('qcm');
        $activite1->setOrdre(2);

        $activite2 = new Activite();
        $activite2->setType('contenu');
        $activite2->setOrdre(1);

        $cours = $this->createMock(Cours::class);
        $cours->method('getActivites')->willReturn(new ArrayCollection([$activite1, $activite2]));

        $result = $this->service->mapToDefaultContentFormat($cours);

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['ordre']);
        $this->assertSame(2, $result[1]['ordre']);
    }
}
