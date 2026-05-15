<?php

namespace App\Tests\Repository;

use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\ProgressionFactory;
use App\Repository\ProgressionRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProgressionRepositoryTest extends WebTestCase
{
    use ResetDatabase;

    private ProgressionRepository $repository;

    protected function setUp(): void
    {
        $client = self::createClient();
        $this->repository = static::getContainer()->get(ProgressionRepository::class);
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
}
