<?php

namespace App\Tests\Repository;

use App\Factory\ClasseFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProgressionFactory;
use App\Repository\ProgressionRepository;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProgressionRepositoryTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, GetsContainerServices;

    private ProgressionRepository $repository;

    protected function setUp(): void
    {
        $this->getCustomClient();
        $this->repository = $this->getService(ProgressionRepository::class);
    }

    public function testRemoveWithoutFlush(): void
    {
        $eleve = EleveFactory::createOne();
        $progression = ProgressionFactory::createOne(['eleve' => $eleve]);

        $this->repository->remove($progression->_real(), false);

        $this->assertTrue(true);
    }

    public function testRemoveWithFlush(): void
    {
        $eleve = EleveFactory::createOne();
        $progression = ProgressionFactory::createOne(['eleve' => $eleve]);
        $id = $progression->getId();

        $this->repository->remove($progression->_real(), true);

        $this->assertNull($this->repository->find($id));
    }

    public function testGetAverageProgressionFromCourses(): void
    {
        $cours1 = CoursFactory::createOne();
        $cours2 = CoursFactory::createOne();
        $eleve = EleveFactory::createOne();

        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours1, 'percentage' => 40]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours2, 'percentage' => 80]);

        $result = $this->repository->getAverageProgressionFromCourses([
            $cours1->_real(),
            $cours2->_real(),
        ]);

        $this->assertNotEmpty($result);
    }

    public function testGetAverageProgressionFromEmptyCourseList(): void
    {
        $result = $this->repository->getAverageProgressionFromCourses([]);

        $this->assertIsArray($result);
    }

    public function testGetBestStudentsReturnsEmptyWhenNoProgressions(): void
    {
        $result = $this->repository->getBestStudents(5);

        $this->assertSame([], $result);
    }

    public function testGetBestStudentsRespectsLimit(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $eleve = EleveFactory::createOne();
            ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => $i * 10]);
        }

        $result = $this->repository->getBestStudents(2);

        $this->assertCount(2, $result);
    }

    public function testGetBestStudentsOrdersByAverageDescending(): void
    {
        $eleveA = EleveFactory::createOne();
        $eleveB = EleveFactory::createOne();
        ProgressionFactory::createOne(['eleve' => $eleveA, 'percentage' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'percentage' => 80]);

        $result = $this->repository->getBestStudents(2);

        $this->assertCount(2, $result);
        $this->assertSame(80.0, $result[0]["average"]);
        $this->assertSame(20.0, $result[1]["average"]);
    }

    public function testGetBestStudentsIncludesStudentWithoutClasse(): void
    {
        $eleve = EleveFactory::createOne(['classe' => null]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 50]);

        $result = $this->repository->getBestStudents(1);

        $this->assertCount(1, $result);
        $this->assertNull($result[0]['classe']);
    }

    public function testGetBestStudentsIncludesClasseName(): void
    {
        $classe = ClasseFactory::createOne(['nom' => 'Première B']);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 75]);

        $result = $this->repository->getBestStudents(1);

        $this->assertCount(1, $result);
        $this->assertSame('Première B', $result[0]['classe']);
    }
}
