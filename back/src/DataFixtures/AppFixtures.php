<?php

namespace App\DataFixtures;

use App\Entity\Activite;
use App\Entity\Badge;
use App\Entity\Classe;
use App\Entity\Competence;
use App\Entity\Contenu;
use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\EleveCompetence;
use App\Entity\Matiere;
use App\Entity\Professeur;
use App\Entity\Progression;
use App\Entity\Qcm;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Factory\ActiviteFactory;
use App\Factory\BadgeFactory;
use App\Factory\ClasseFactory;
use App\Factory\CompetenceFactory;
use App\Factory\ContenuFactory;
use App\Factory\CoursFactory;
use App\Factory\EleveCompetenceFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use App\Factory\QcmFactory;
use App\Factory\QuestionFactory;
use App\Factory\ReponseFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private \Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // ── Matières ──
        MatiereFactory::createFromBase();

        // ── Badges ──
        BadgeFactory::createFromBase();

        // ── Professeurs (5) ──
        ProfesseurFactory::createMany(5, fn(int $i) => [
            'email' => "professeur{$i}@ranked.fr",
        ]);

        // ── Classes (10) ──
        ClasseFactory::createMany(10);

        // ── Élèves (50) ──
        EleveFactory::createMany(50, fn(int $i) => [
            'email' => "eleve{$i}@ranked.fr",
        ]);

        // ── Cours (20) ──
        CoursFactory::createMany(20);

        // ── Compétences (40) ──
        CompetenceFactory::createMany(40);

        // ── Activités — with contenu or qcm per cours ──
        foreach (CoursFactory::repository()->findAll() as $cours) {
            $nbActivites = $this->faker->numberBetween(2, 5);
            for ($j = 0; $j < $nbActivites; $j++) {
                $type = $this->faker->randomElement(['contenu', 'qcm']);

                $activite = ActiviteFactory::createOne([
                    'type'  => $type,
                    'cours' => $cours,
                    'ordre' => $j + 1
                ]);

                if ($type === 'contenu') {
                    ContenuFactory::createOne(['activite' => $activite]);
                } else {
                    $qcm = QcmFactory::createOne(['activite' => $activite]);

                    $nbQuestions = $this->faker->numberBetween(2, 5);
                    for ($q = 0; $q < $nbQuestions; $q++) {
                        $question = QuestionFactory::createOne(['qcm' => $qcm]);

                        $correctIndex = $this->faker->numberBetween(0, 3);
                        for ($r = 0; $r < 4; $r++) {
                            ReponseFactory::createOne([
                                'question'  => $question,
                                'isCorrect' => $r === $correctIndex,
                            ]);
                        }
                    }
                }
            }
        }

        // ── Progressions (100) ──
        ProgressionFactory::createMany(100);

        // ── EleveCompetences (80, no duplicate pairs) ──
        $eleves      = EleveFactory::repository()->findAll();
        $competences = CompetenceFactory::repository()->findAll();
        $usedPairs   = [];
        $created     = 0;

        while ($created < 80) {
            $eleve      = $this->faker->randomElement($eleves);
            $competence = $this->faker->randomElement($competences);
            $pairKey    = spl_object_id($eleve) . '-' . spl_object_id($competence);

            if (in_array($pairKey, $usedPairs)) {
                continue;
            }

            $usedPairs[] = $pairKey;
            EleveCompetenceFactory::createOne([
                'eleve'      => $eleve,
                'competence' => $competence,
            ]);
            $created++;
        }
    }
}
