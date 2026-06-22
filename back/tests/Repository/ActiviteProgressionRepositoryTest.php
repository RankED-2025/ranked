<?php

namespace App\Tests\Repository;

use App\Factory\ActiviteFactory;
use App\Factory\ActiviteProgressionFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Repository\ActiviteProgressionRepository;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ActiviteProgressionRepositoryTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, GetsContainerServices;

    private ActiviteProgressionRepository $repository;

    protected function setUp(): void
    {
        $this->getCustomClient();
        $this->repository = $this->getService(ActiviteProgressionRepository::class);
    }

    public function testSaveWithoutFlush(): void
    {
        $eleve = EleveFactory::createOne();
        $activite = ActiviteFactory::createOne();

        $activiteProgression = ActiviteProgressionFactory::new()->withoutPersisting()->create([
            'eleve' => $eleve,
            'activite' => $activite,
        ]);

        $this->repository->save($activiteProgression->_real(), false);

        // check if the entity has not been flushed
        $queryResult = $this
            ->getService(ActiviteProgressionRepository::class)
            ->createQueryBuilder("a")
            ->getQuery()
            ->getArrayResult();

        $this->assertCount(0, $queryResult);
    }

    public function testSaveWithFlush(): void
    {
        $eleve = EleveFactory::createOne();
        $activite = ActiviteFactory::createOne();

        $activiteProgression = ActiviteProgressionFactory::new()->withoutPersisting()->create([
            'eleve' => $eleve,
            'activite' => $activite,
        ]);

        $this->repository->save($activiteProgression->_real(), true);

        $this->assertSame(
            $activiteProgression->getId(),
            $this->repository->find($activiteProgression->getId())->getId()
        );

        $queryResult = $this->repository
            ->createQueryBuilder('a')
            ->getQuery()
            ->getArrayResult();

        $this->assertCount(1, $queryResult);
    }

    public function testRemoveWithoutFlush(): void
    {
        $activiteProgression = ActiviteProgressionFactory::createOne();

        $this->repository->remove($activiteProgression->_real(), false);

        // check if the entity has not been deleted from the DB (flush)
        $queryResult = $this
            ->getService(ActiviteProgressionRepository::class)
            ->createQueryBuilder("a")
            ->getQuery()
            ->getResult();

        $this->assertCount(1, $queryResult);
        $this->assertSame($activiteProgression->getId(), $queryResult[0]->getId());
    }

    public function testRemoveWithFlush(): void
    {
        $activiteProgression = ActiviteProgressionFactory::createOne();
        $id = $activiteProgression->getId();

        $this->repository->remove($activiteProgression->_real(), true);

        $this->assertNull($this->repository->find($id));

        $queryResult = $this->repository
            ->createQueryBuilder('a')
            ->getQuery()
            ->getArrayResult();

        $this->assertCount(0, $queryResult);
    }

    public function testFindCompletedActiviteIdsReturnsOnlyCompletedActivitesForEleveAndCours(): void
    {
        $eleve = EleveFactory::createOne();
        $cours = CoursFactory::createOne();

        $completedActivite = ActiviteFactory::createOne(['cours' => $cours]);
        $notCompletedActivite = ActiviteFactory::createOne(['cours' => $cours]);
        $otherCoursActivite = ActiviteFactory::createOne(['cours' => CoursFactory::createOne()]);

        ActiviteProgressionFactory::createOne([
            'eleve' => $eleve,
            'activite' => $completedActivite,
            'completedAt' => new \DateTimeImmutable(),
        ]);

        ActiviteProgressionFactory::createOne([
            'eleve' => $eleve,
            'activite' => $notCompletedActivite,
            'completedAt' => null,
        ]);

        ActiviteProgressionFactory::createOne([
            'eleve' => $eleve,
            'activite' => $otherCoursActivite,
            'completedAt' => new \DateTimeImmutable(),
        ]);

        $result = $this->repository->findCompletedActiviteIds($eleve->_real(), $cours->_real());

        $this->assertCount(1, $result);
        $this->assertSame($completedActivite->getId(), $result[0]);
        $this->assertNotContains($notCompletedActivite->getId(), $result);
        $this->assertNotContains($otherCoursActivite->getId(), $result);
    }

    public function testFindCompletedActiviteIdsReturnsEmptyArrayWhenNoneCompleted(): void
    {
        $eleve = EleveFactory::createOne();
        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours]);

        ActiviteProgressionFactory::createOne([
            'eleve' => $eleve,
            'activite' => $activite,
            'completedAt' => null,
        ]);

        $result = $this->repository->findCompletedActiviteIds($eleve->_real(), $cours->_real());

        $this->assertSame([], $result);
    }

    public function testFindCompletedActiviteIdsDoesNotReturnOtherElevesActivites(): void
    {
        $eleve = EleveFactory::createOne();
        $otherEleve = EleveFactory::createOne();
        $cours = CoursFactory::createOne();
        $activite = ActiviteFactory::createOne(['cours' => $cours]);

        ActiviteProgressionFactory::createOne([
            'eleve' => $otherEleve,
            'activite' => $activite,
            'completedAt' => new \DateTimeImmutable(),
        ]);

        $result = $this->repository->findCompletedActiviteIds($eleve->_real(), $cours->_real());

        $this->assertSame([], $result);
    }
}
