<?php

namespace App\Tests\Repository;

use App\Factory\CompetenceFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveCompetenceFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProgressionFactory;
use App\Repository\CompetenceRepository;
use App\Tests\Traits\GetsContainerServices;
use App\Tests\Traits\MakesHttpRequests;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class CompetenceRepositoryTest extends WebTestCase
{
    use ResetDatabase, MakesHttpRequests, GetsContainerServices;

    private CompetenceRepository $repository;

    protected function setUp(): void
    {
        $this->getCustomClient();
        $this->repository = $this->getService(CompetenceRepository::class);
    }

    public function testGetStudentCompetencesDetailReturnsEmptyWhenNoProgressions(): void
    {
        $eleve = EleveFactory::createOne();

        $result = $this->repository->getStudentCompetencesDetail($eleve->_real());

        $this->assertSame([], $result);
    }

    public function testGetStudentCompetencesDetailReturnsEmptyWhenProgressionExistsButNoCourseCompetences(): void
    {
        $eleve = EleveFactory::createOne();
        $cours = CoursFactory::createOne();
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);

        $result = $this->repository->getStudentCompetencesDetail($eleve->_real());

        $this->assertSame([], $result);
    }

    public function testGetStudentCompetencesDetailReturnsCorrectFields(): void
    {
        $eleve = EleveFactory::createOne();
        $matiere = MatiereFactory::createOne(['libelle' => 'Mathématiques']);
        $cours = CoursFactory::createOne(['titre' => 'Algèbre', 'matiere' => $matiere]);
        $competence = CompetenceFactory::createOne(['nom' => 'Résoudre des équations', 'niveau' => 'débutant', 'cours' => $cours]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours, 'percentage' => 50]);

        $result = $this->repository->getStudentCompetencesDetail($eleve->_real());

        $this->assertCount(1, $result);
        $this->assertSame('Résoudre des équations', $result[0]['nom']);
        $this->assertSame('débutant', $result[0]['niveau']);
        $this->assertSame('Algèbre', $result[0]['courseTitle']);
        $this->assertSame('Mathématiques', $result[0]['matiere']);
        $this->assertSame($competence->_real()->getId(), $result[0]["id"]);
        $this->assertSame($cours->_real()->getId(), $result[0]["courseId"]);
        $this->assertFalse($result[0]['acquired']);
    }

    public function testGetStudentCompetencesDetailAcquiredIsFalseWithoutEleveCompetence(): void
    {
        $eleve = EleveFactory::createOne();
        $cours = CoursFactory::createOne();
        CompetenceFactory::createOne(['cours' => $cours]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);

        $result = $this->repository->getStudentCompetencesDetail($eleve->_real());

        $this->assertCount(1, $result);
        $this->assertFalse($result[0]['acquired']);
        $this->assertIsBool($result[0]['acquired']);
    }

    public function testGetStudentCompetencesDetailAcquiredIsTrueWithEleveCompetence(): void
    {
        $eleve = EleveFactory::createOne();
        $cours = CoursFactory::createOne();
        $competence = CompetenceFactory::createOne(['cours' => $cours]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);
        EleveCompetenceFactory::createOne(['eleve' => $eleve, 'competence' => $competence]);

        $result = $this->repository->getStudentCompetencesDetail($eleve->_real());

        $this->assertCount(1, $result);
        $this->assertTrue($result[0]['acquired']);
        $this->assertIsBool($result[0]['acquired']);
    }

    public function testGetStudentCompetencesDetailExcludesCoursesWithoutProgression(): void
    {
        $eleve = EleveFactory::createOne();
        $coursWithProgression = CoursFactory::createOne();
        $coursWithoutProgression = CoursFactory::createOne();
        CompetenceFactory::createOne(['cours' => $coursWithProgression]);
        CompetenceFactory::createOne(['cours' => $coursWithoutProgression]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $coursWithProgression]);

        $result = $this->repository->getStudentCompetencesDetail($eleve->_real());

        $this->assertCount(1, $result);
        $this->assertSame($coursWithProgression->_real()->getId(), $result[0]["courseId"]);
    }

    public function testGetStudentCompetencesDetailDoesNotMarkOtherStudentsCompetencesAsAcquired(): void
    {
        $eleveA = EleveFactory::createOne();
        $eleveB = EleveFactory::createOne();
        $cours = CoursFactory::createOne();
        $competence = CompetenceFactory::createOne(['cours' => $cours]);
        ProgressionFactory::createOne(['eleve' => $eleveA, 'cours' => $cours]);
        EleveCompetenceFactory::createOne(['eleve' => $eleveB, 'competence' => $competence]);

        $result = $this->repository->getStudentCompetencesDetail($eleveA->_real());

        $this->assertCount(1, $result);
        $this->assertFalse($result[0]['acquired']);
    }

    public function testGetStudentCompetencesDetailOnlyReturnsCurrentStudentData(): void
    {
        $eleveA = EleveFactory::createOne();
        $eleveB = EleveFactory::createOne();
        $coursA = CoursFactory::createOne();
        $coursB = CoursFactory::createOne();
        $competenceA = CompetenceFactory::createOne(['cours' => $coursA]);
        CompetenceFactory::createOne(['cours' => $coursB]);
        ProgressionFactory::createOne(['eleve' => $eleveA, 'cours' => $coursA]);
        ProgressionFactory::createOne(['eleve' => $eleveB, 'cours' => $coursB]);

        $result = $this->repository->getStudentCompetencesDetail($eleveA->_real());

        $this->assertCount(1, $result);
        $this->assertSame($competenceA->_real()->getId(), $result[0]['id']);
        $this->assertSame($coursA->_real()->getId(), $result[0]['courseId']);
    }

    public function testGetStudentCompetencesDetailReturnsMultipleCompetencesForOneCourse(): void
    {
        $eleve = EleveFactory::createOne();
        $cours = CoursFactory::createOne();
        CompetenceFactory::createMany(3, ['cours' => $cours]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);

        $result = $this->repository->getStudentCompetencesDetail($eleve->_real());

        $this->assertCount(3, $result);
    }

    public function testGetStudentCompetencesDetailMixedAcquiredAndNotAcquired(): void
    {
        $eleve = EleveFactory::createOne();
        $cours = CoursFactory::createOne();
        $acquiredCompetence = CompetenceFactory::createOne(['cours' => $cours]);
        CompetenceFactory::createOne(['cours' => $cours]);
        ProgressionFactory::createOne(['eleve' => $eleve, 'cours' => $cours]);
        EleveCompetenceFactory::createOne(['eleve' => $eleve, 'competence' => $acquiredCompetence]);

        $result = $this->repository->getStudentCompetencesDetail($eleve->_real());

        $this->assertCount(2, $result);

        $acquiredCount = count(array_filter($result, fn($r) => $r['acquired']));
        $notAcquiredCount = count(array_filter($result, fn($r) => !$r['acquired']));

        $this->assertSame(1, $acquiredCount);
        $this->assertSame(1, $notAcquiredCount);
    }

    public function testGetTotalByMatiereReturnsZeroWhenNoCompetences(): void
    {
        $result = $this->repository->getTotalByMatiere();

        $this->assertSame([], $result);
    }

    public function testGetTotalByMatiereGroupsByMatiere(): void
    {
        $matiereA = MatiereFactory::createOne(['libelle' => 'Maths']);
        $matiereB = MatiereFactory::createOne(['libelle' => 'Français']);
        $coursA = CoursFactory::createOne(['matiere' => $matiereA]);
        $coursB = CoursFactory::createOne(['matiere' => $matiereB]);
        CompetenceFactory::createMany(3, ['cours' => $coursA]);
        CompetenceFactory::createMany(2, ['cours' => $coursB]);

        $result = $this->repository->getTotalByMatiere();

        $this->assertCount(2, $result);
        $byMatiere = array_column($result, 'total', 'matiere');
        $this->assertSame('3', (string) $byMatiere['Maths']);
        $this->assertSame('2', (string) $byMatiere['Français']);
    }
}
