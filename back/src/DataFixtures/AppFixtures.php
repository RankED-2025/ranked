<?php

namespace App\DataFixtures;

use App\Factory\ActiviteFactory;
use App\Factory\ActiviteProgressionFactory;
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
use App\Factory\DifficulteFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private \Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        ini_set('memory_limit', '1G');
        // ── Matières ──
        MatiereFactory::createFromBase();

        // ── Difficultés ──
        DifficulteFactory::createFromBase();

        // ── Badges ──
        BadgeFactory::createFromBase();

        // ── Professeurs (5) ──
        ProfesseurFactory::createMany(5, fn(int $i) => [
            'email' => "professeur$i@ranked.fr",
        ]);

        // ── Classes (10) ──
        ClasseFactory::createMany(10);

        // ── Élèves (50) ──
        $classes = ClasseFactory::repository()->findAll();
        EleveFactory::createMany(50, fn(int $i) => [
            'email'  => "eleve$i@ranked.fr",
            'classe' => $this->faker->randomElement($classes),
        ]);

        // ── Admins : 1 prof + 5 élèves au hasard ──
        ProfesseurFactory::random()->_set('roles', ['ROLE_PROFESSEUR', 'ROLE_ADMIN'])->_save();

        foreach (EleveFactory::randomRange(5, 5) as $eleve) {
            $eleve->_set('roles', ['ROLE_ELEVE', 'ROLE_ADMIN'])->_save();
        }

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

        // ── ActiviteProgressions (dérivées des progressions) ──
        $usedActivitePairs = [];
        foreach (ProgressionFactory::repository()->findAll() as $progression) {
            $eleve = $progression->getEleve();
            $activites = $progression->getCours()->getActivites()->toArray();

            usort($activites, fn($a, $b) => ($a->getOrdre() ?? 0) <=> ($b->getOrdre() ?? 0));

            $nbCompleted = (int) round(count($activites) * $progression->getPercentage() / 100);

            foreach (array_slice($activites, 0, $nbCompleted) as $activite) {
                $pairKey = spl_object_id($eleve) . '-' . spl_object_id($activite);

                if (in_array($pairKey, $usedActivitePairs)) {
                    continue;
                }
                $usedActivitePairs[] = $pairKey;

                ActiviteProgressionFactory::createOne([
                    'eleve'    => $eleve,
                    'activite' => $activite,
                ]);
            }
        }

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
