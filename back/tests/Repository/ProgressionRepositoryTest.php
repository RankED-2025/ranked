<?php

namespace App\Tests\Repository;

use App\Factory\BadgeFactory;
use App\Factory\ClasseFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProfesseurFactory;
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
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);

        $result = $this->repository->getBestStudents(5, $classe->_real(), $prof->_real());

        $this->assertSame([], $result);
    }

    public function testGetBestStudentsRespectsLimit(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        for ($i = 0; $i < 4; $i++) {
            $eleve = EleveFactory::createOne(['classe' => $classe]);
            ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => $i * 10]);
        }

        $result = $this->repository->getBestStudents(2, $classe->_real(), $prof->_real());

        $this->assertCount(2, $result);
    }

    public function testGetBestStudentsOrdersByAverageDescending(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $eleveA = EleveFactory::createOne(['classe' => $classe]);
        $eleveB = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleveA, 'cours' => $cours, 'percentage' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'cours' => $cours, 'percentage' => 80]);

        $result = $this->repository->getBestStudents(2, $classe->_real(), $prof->_real());

        $this->assertCount(2, $result);
        $this->assertSame(80.0, (float) $result[0]['average']);
        $this->assertSame(20.0, (float) $result[1]['average']);
    }

    public function testGetBestStudentsCalculatesAverageProgressionForEachEleve()
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $eleveA = EleveFactory::createOne(['classe' => $classe]);
        $eleveB = EleveFactory::createOne(['classe' => $classe]);

        // progressions for each students
        //expected avg: 30
        ProgressionFactory::createOne(['eleve' => $eleveA, 'cours' => $cours, 'percentage' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleveA, 'cours' => $cours, 'percentage' => 40]);

        //expected avg: 45.5
        ProgressionFactory::createOne(['eleve' => $eleveB, 'cours' => $cours, 'percentage' => 78]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'cours' => $cours, 'percentage' => 13]);

        $result = $this->repository->getBestStudents(2, $classe->_real(), $prof->_real());

        $this->assertSame(45.5, (float) $result[0]['average']);
        $this->assertSame(30.0, (float) $result[1]['average']);
    }

    public function testGetBestStudentsExcludesStudentsFromOtherClasses(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classeA = ClasseFactory::createOne(['professeur' => $prof]);
        $classeB = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $eleveA = EleveFactory::createOne(['classe' => $classeA]);
        $eleveB = EleveFactory::createOne(['classe' => $classeB]);
        ProgressionFactory::createOne(['eleve' => $eleveA, 'cours' => $cours, 'percentage' => 50]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'cours' => $cours, 'percentage' => 90]);

        $result = $this->repository->getBestStudents(5, $classeA->_real(), $prof->_real());

        $this->assertCount(1, $result);
        $this->assertSame((string) $eleveA->getId(), (string) $result[0]['eleveId']);
    }

    public function testGetBestStudentsExcludesCoursesFromOtherProfessors(): void
    {
        $prof = ProfesseurFactory::createOne();
        $otherProf = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $ownCours = CoursFactory::createOne(['professeur' => $prof]);
        $otherCours = CoursFactory::createOne(['professeur' => $otherProf]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);

        // a progression can end up pointing to a course owned by another professor
        // (e.g. stale/inconsistent data) even though it is scoped to this classe
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $ownCours, 'classe' => $classe, 'percentage' => 40]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $otherCours, 'classe' => $classe, 'percentage' => 100]);

        $result = $this->repository->getBestStudents(5, $classe->_real(), $prof->_real());

        $this->assertCount(1, $result);
        $this->assertSame(40.0, (float) $result[0]['average']);
        $this->assertSame(1, (int) $result[0]['totalCourses']);
    }

    public function testGetBestStudentsExcludesProgressionsAssignedThroughAnotherClass(): void
    {
        $prof = ProfesseurFactory::createOne();
        $oldClasse = ClasseFactory::createOne(['professeur' => $prof]);
        $newClasse = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);

        // the student used to be in $oldClasse and was assigned a course there,
        // then moved to $newClasse: that old progression must not leak into $newClasse.
        $eleve = EleveFactory::createOne(['classe' => $newClasse]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'classe' => $oldClasse, 'percentage' => 90]);

        $result = $this->repository->getBestStudents(5, $newClasse->_real(), $prof->_real());

        $this->assertSame([], $result);
    }

    public function testGetBestStudentsReturnsNameAndFirstname(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe, 'name' => 'Martin', 'firstname' => 'Bob']);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 75]);

        $result = $this->repository->getBestStudents(1, $classe->_real(), $prof->_real());

        $this->assertCount(1, $result);
        $this->assertSame('Martin', $result[0]['name']);
        $this->assertSame('Bob', $result[0]['firstname']);
    }

    public function testGetBestStudentsReturnsTotalCourses(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 40]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 80]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 100]);

        $result = $this->repository->getBestStudents(1, $classe->_real(), $prof->_real());

        $this->assertCount(1, $result);
        $this->assertSame(3, (int) $result[0]['totalCourses']);
    }

    public function testGetBestStudentsReturnsCompletedCourses(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 50]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 100]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 100]);

        $result = $this->repository->getBestStudents(1, $classe->_real(), $prof->_real());

        $this->assertCount(1, $result);
        $this->assertSame(2, (int) $result[0]['completedCourses']);
    }

    public function testGetBestStudentsCompletedCoursesIsZeroWhenNoneFinished(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $cours = CoursFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 50]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 99]);

        $result = $this->repository->getBestStudents(1, $classe->_real(), $prof->_real());

        $this->assertCount(1, $result);
        $this->assertSame(0, (int) $result[0]['completedCourses']);
    }

    public function testGetBestStudentTopSubjectsReturnsEmptyForEmptyIds(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);

        $result = $this->repository->getBestStudentTopSubjects([], $classe->_real(), $prof->_real());

        $this->assertSame([], $result);
    }

    public function testGetBestStudentTopSubjectsReturnsTopSubjectPerStudent(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);

        $matiereA = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $matiereB = MatiereFactory::createOne(['libelle' => 'Français']);
        $coursA = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiereA]);
        $coursB = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiereB]);

        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursA, 'percentage' => 90]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursB, 'percentage' => 50]);

        $result = $this->repository->getBestStudentTopSubjects([$eleve->getId()], $classe->_real(), $prof->_real());

        $this->assertNotEmpty($result);
        $firstEntry = $result[0];
        $this->assertSame((string) $eleve->getId(), (string) $firstEntry['eleveId']);
        $this->assertSame('Mathématiques', $firstEntry['subject']);
    }

    public function testGetBestStudentTopSubjectsOrdersBySubjectAverageDescending(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);

        $matiereA = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $matiereB = MatiereFactory::createOne(['libelle' => 'Français']);
        $coursA = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiereA]);
        $coursB = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiereB]);

        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursA, 'percentage' => 30]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursB, 'percentage' => 80]);

        $result = $this->repository->getBestStudentTopSubjects([$eleve->getId()], $classe->_real(), $prof->_real());

        $this->assertCount(2, $result);
        $this->assertSame('Français', $result[0]['subject']);
        $this->assertSame('Mathématiques', $result[1]['subject']);
    }

    public function testGetBestStudentTopSubjectsExcludesCoursesFromOtherProfessors(): void
    {
        $prof = ProfesseurFactory::createOne();
        $otherProf = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $eleve = EleveFactory::createOne(['classe' => $classe]);

        $matiereOwn = MatiereFactory::createOne(['libelle' => 'Physique']);
        $matiereOther = MatiereFactory::createOne(['libelle' => 'Chimie']);
        $ownCours = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiereOwn]);
        $otherCours = CoursFactory::createOne(['professeur' => $otherProf, 'matiere' => $matiereOther]);

        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $ownCours, 'classe' => $classe, 'percentage' => 40]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $otherCours, 'classe' => $classe, 'percentage' => 95]);

        $result = $this->repository->getBestStudentTopSubjects([$eleve->getId()], $classe->_real(), $prof->_real());

        $this->assertCount(1, $result);
        $this->assertSame('Physique', $result[0]['subject']);
    }

    public function testGetStudentBadgesDetailReturnsEmptyWhenNoProgressions(): void
    {
        $eleve = EleveFactory::createOne();

        $result = $this->repository->getStudentBadgesDetail($eleve->_real());

        $this->assertSame([], $result);
    }

    public function testGetStudentBadgesDetailReturnsCorrectFields(): void
    {
        $eleve = EleveFactory::createOne();
        $badge = BadgeFactory::createOne(['type' => 'bronze', 'label' => 'Bronze']);
        $cours = CoursFactory::createOne(['titre' => 'Cours Test']);
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge, 'cours' => $cours, 'percentage' => 50]);

        $result = $this->repository->getStudentBadgesDetail($eleve->_real());

        $this->assertCount(1, $result);
        $this->assertSame('Cours Test', $result[0]['courseTitle']);
        $this->assertSame('bronze', $result[0]['badgeType']);
        $this->assertSame('Bronze', $result[0]['badgeLabel']);
        $this->assertSame(50, (int) $result[0]['percentage']);
        $this->assertSame($cours->getId(), $result[0]['courseId']);
    }

    public function testGetStudentBadgesDetailOrdersByPercentageDescending(): void
    {
        $eleve = EleveFactory::createOne();
        $badge = BadgeFactory::createOne();
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 20]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 100]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 60]);

        $result = $this->repository->getStudentBadgesDetail($eleve->_real());

        $this->assertCount(3, $result);
        $this->assertSame(100, (int) $result[0]['percentage']);
        $this->assertSame(60, (int) $result[1]['percentage']);
        $this->assertSame(20, (int) $result[2]['percentage']);
    }

    public function testGetStudentBadgesDetailFiltersByEleve(): void
    {
        $eleveA = EleveFactory::createOne();
        $eleveB = EleveFactory::createOne();
        $badge = BadgeFactory::createOne();
        ProgressionFactory::createOne(['eleve' => $eleveA, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 40]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'badge' => $badge, 'cours' => CoursFactory::createOne(), 'percentage' => 90]);

        $result = $this->repository->getStudentBadgesDetail($eleveA->_real());

        $this->assertCount(1, $result);

        // no eleve ID check possible because the returned array doesn't include "elevedId" as a key,
        // nor any similar keys that can provide the ID the eleve in the query's relation.
        $this->assertSame(40, (int) $result[0]['percentage']);
    }

    public function testGetBestStudentTopSubjectsFiltersOnlyGivenStudents(): void
    {
        $prof = ProfesseurFactory::createOne();
        $classe = ClasseFactory::createOne(['professeur' => $prof]);
        $eleveA = EleveFactory::createOne(['classe' => $classe]);
        $eleveB = EleveFactory::createOne(['classe' => $classe]);

        $matiere = MatiereFactory::createOne(['libelle' => 'Anglais']);
        $cours = CoursFactory::createOne(['professeur' => $prof, 'matiere' => $matiere]);

        ProgressionFactory::createOne(['eleve' => $eleveA, 'cours' => $cours, 'percentage' => 70]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'cours' => $cours, 'percentage' => 80]);

        $result = $this->repository->getBestStudentTopSubjects([$eleveA->getId()], $classe->_real(), $prof->_real());

        $this->assertCount(1, $result);
        $this->assertSame((string) $eleveA->getId(), (string) $result[0]['eleveId']);
    }
}
