<?php

namespace App\Tests\Repository;

use App\Factory\ClasseFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
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
        $classe = ClasseFactory::createOne();

        $result = $this->repository->getBestStudents(5, $classe->_real());

        $this->assertSame([], $result);
    }

    public function testGetBestStudentsRespectsLimit(): void
    {
        $classe = ClasseFactory::createOne();
        for ($i = 0; $i < 4; $i++) {
            $eleve = EleveFactory::createOne(['classe' => $classe]);
            ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => $i * 10]);
        }

        $result = $this->repository->getBestStudents(2, $classe->_real());

        $this->assertCount(2, $result);
    }

    public function testGetBestStudentsOrdersByAverageDescending(): void
    {
        $classe = ClasseFactory::createOne();
        $eleveA = EleveFactory::createOne(['classe' => $classe]);
        $eleveB = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleveA, 'percentage' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'percentage' => 80]);

        $result = $this->repository->getBestStudents(2, $classe->_real());

        $this->assertCount(2, $result);
        $this->assertSame(80.0, (float) $result[0]['average']);
        $this->assertSame(20.0, (float) $result[1]['average']);
    }

    public function testGetBestStudentsCalculatesAverageProgressionForEachEleve()
    {
        $classe = ClasseFactory::createOne();
        $eleveA = EleveFactory::createOne(['classe' => $classe]);
        $eleveB = EleveFactory::createOne(['classe' => $classe]);

        // progressions for each students
        //expected avg: 30
        ProgressionFactory::createOne(['eleve' => $eleveA, 'percentage' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleveA, 'percentage' => 40]);

        //expected avg: 45.5
        ProgressionFactory::createOne(['eleve' => $eleveB, 'percentage' => 78]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'percentage' => 13]);

        $result = $this->repository->getBestStudents(2, $classe->_real());

        $this->assertSame(45.5, (float) $result[0]['average']);
        $this->assertSame(30.0, (float) $result[1]['average']);
    }

    public function testGetBestStudentsExcludesStudentsFromOtherClasses(): void
    {
        $classeA = ClasseFactory::createOne();
        $classeB = ClasseFactory::createOne();
        $eleveA = EleveFactory::createOne(['classe' => $classeA]);
        $eleveB = EleveFactory::createOne(['classe' => $classeB]);
        ProgressionFactory::createOne(['eleve' => $eleveA, 'percentage' => 50]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'percentage' => 90]);

        $result = $this->repository->getBestStudents(5, $classeA->_real());

        $this->assertCount(1, $result);
        $this->assertSame((string) $eleveA->getId(), (string) $result[0]['eleveId']);
    }

    public function testGetBestStudentsReturnsNameAndFirstname(): void
    {
        $classe = ClasseFactory::createOne();
        $eleve = EleveFactory::createOne(['classe' => $classe, 'name' => 'Martin', 'firstname' => 'Bob']);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 75]);

        $result = $this->repository->getBestStudents(1, $classe->_real());

        $this->assertCount(1, $result);
        $this->assertSame('Martin', $result[0]['name']);
        $this->assertSame('Bob', $result[0]['firstname']);
    }

    public function testGetBestStudentsReturnsTotalCourses(): void
    {
        $classe = ClasseFactory::createOne();
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 40]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 80]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 100]);

        $result = $this->repository->getBestStudents(1, $classe->_real());

        $this->assertCount(1, $result);
        $this->assertSame(3, (int) $result[0]['totalCourses']);
    }

    public function testGetBestStudentsReturnsCompletedCourses(): void
    {
        $classe = ClasseFactory::createOne();
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 50]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 100]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 100]);

        $result = $this->repository->getBestStudents(1, $classe->_real());

        $this->assertCount(1, $result);
        $this->assertSame(2, (int) $result[0]['completedCourses']);
    }

    public function testGetBestStudentsCompletedCoursesIsZeroWhenNoneFinished(): void
    {
        $classe = ClasseFactory::createOne();
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 50]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'percentage' => 99]);

        $result = $this->repository->getBestStudents(1, $classe->_real());

        $this->assertCount(1, $result);
        $this->assertSame(0, (int) $result[0]['completedCourses']);
    }

    public function testGetBestStudentTopSubjectsReturnsEmptyForEmptyIds(): void
    {
        $result = $this->repository->getBestStudentTopSubjects([]);

        $this->assertSame([], $result);
    }

    public function testGetBestStudentTopSubjectsReturnsTopSubjectPerStudent(): void
    {
        $classe = ClasseFactory::createOne();
        $eleve = EleveFactory::createOne(['classe' => $classe]);

        $matiereA = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $matiereB = MatiereFactory::createOne(['libelle' => 'Français']);
        $coursA = CoursFactory::createOne(['matiere' => $matiereA]);
        $coursB = CoursFactory::createOne(['matiere' => $matiereB]);

        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursA, 'percentage' => 90]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursB, 'percentage' => 50]);

        $result = $this->repository->getBestStudentTopSubjects([$eleve->getId()]);

        $this->assertNotEmpty($result);
        $firstEntry = $result[0];
        $this->assertSame((string) $eleve->getId(), (string) $firstEntry['eleveId']);
        $this->assertSame('Mathématiques', $firstEntry['subject']);
    }

    public function testGetBestStudentTopSubjectsOrdersBySubjectAverageDescending(): void
    {
        $classe = ClasseFactory::createOne();
        $eleve = EleveFactory::createOne(['classe' => $classe]);

        $matiereA = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $matiereB = MatiereFactory::createOne(['libelle' => 'Français']);
        $coursA = CoursFactory::createOne(['matiere' => $matiereA]);
        $coursB = CoursFactory::createOne(['matiere' => $matiereB]);

        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursA, 'percentage' => 30]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursB, 'percentage' => 80]);

        $result = $this->repository->getBestStudentTopSubjects([$eleve->getId()]);

        $this->assertCount(2, $result);
        $this->assertSame('Français', $result[0]['subject']);
        $this->assertSame('Mathématiques', $result[1]['subject']);
    }

    public function testGetBestStudentTopSubjectsFiltersOnlyGivenStudents(): void
    {
        $classe = ClasseFactory::createOne();
        $eleveA = EleveFactory::createOne(['classe' => $classe]);
        $eleveB = EleveFactory::createOne(['classe' => $classe]);

        $matiere = MatiereFactory::createOne(['libelle' => 'Anglais']);
        $cours = CoursFactory::createOne(['matiere' => $matiere]);

        ProgressionFactory::createOne(['eleve' => $eleveA, 'cours' => $cours, 'percentage' => 70]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'cours' => $cours, 'percentage' => 80]);

        $result = $this->repository->getBestStudentTopSubjects([$eleveA->getId()]);

        $this->assertCount(1, $result);
        $this->assertSame((string) $eleveA->getId(), (string) $result[0]['eleveId']);
    }
}
