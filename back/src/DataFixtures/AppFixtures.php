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
use App\Factory\CoursFactory;
use App\Factory\EleveCompetenceFactory;
use App\Factory\EleveFactory;
use App\Factory\MatiereFactory;
use App\Factory\ProfesseurFactory;
use App\Factory\ProgressionFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function _load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // ── Matières ──
        $matiereNames = ['Mathématiques', 'Français', 'Histoire-Géographie', 'Physique-Chimie', 'SVT', 'Anglais', 'Espagnol', 'Technologie'];
        $matieres = [];
        foreach ($matiereNames as $name) {
            $matiere = new Matiere();
            $matiere->setLibelle($name);
            $manager->persist($matiere);
            $matieres[] = $matiere;
        }

        // ── Badges ──
        $badgeData = [
            ['type' => 'bronze',   'label' => 'Débutant'],
            ['type' => 'argent',   'label' => 'Intermédiaire'],
            ['type' => 'or',       'label' => 'Avancé'],
            ['type' => 'platine',  'label' => 'Expert'],
            ['type' => 'diamant',  'label' => 'Maître'],
        ];
        $badges = [];
        foreach ($badgeData as $data) {
            $badge = new Badge();
            $badge->setType($data['type']);
            $badge->setLabel($data['label']);
            $manager->persist($badge);
            $badges[] = $badge;
        }

        // ── Professeurs (5) ──
        $professeurs = [];
        for ($i = 0; $i < 5; $i++) {
            $prof = new Professeur();
            $prof->setName($faker->lastName());
            $prof->setFirstname($faker->firstName());
            $prof->setEmail("professeur{$i}@ranked.fr");
            $prof->setPassword($this->passwordHasher->hashPassword($prof, 'password'));
            $prof->setRoles(['ROLE_PROFESSEUR']);
            $manager->persist($prof);
            $professeurs[] = $prof;
        }

        // ── Classes (10) ──
        $niveaux = ['6ème', '5ème', '4ème', '3ème'];
        $sections = ['A', 'B', 'C'];
        $classes = [];
        for ($i = 0; $i < 10; $i++) {
            $classe = new Classe();
            $classe->setNom($faker->randomElement($niveaux) . ' ' . $faker->randomElement($sections));
            $classe->setProfesseur($faker->randomElement($professeurs));
            $manager->persist($classe);
            $classes[] = $classe;
        }

        // ── Élèves (50) ──
        $eleves = [];
        for ($i = 0; $i < 50; $i++) {
            $eleve = new Eleve();
            $eleve->setName($faker->lastName());
            $eleve->setFirstname($faker->firstName());
            $eleve->setEmail("eleve{$i}@ranked.fr");
            $eleve->setPassword($this->passwordHasher->hashPassword($eleve, 'password'));
            $eleve->setRoles(['ROLE_ELEVE']);
            $eleve->setClasse($faker->randomElement($classes));
            $manager->persist($eleve);
            $eleves[] = $eleve;
        }

        // ── Cours (20) ──
        $cours = [];
        for ($i = 0; $i < 20; $i++) {
            $c = new Cours();
            $c->setProfesseur($faker->randomElement($professeurs));
            $c->setMatiere($faker->randomElement($matieres));
            $manager->persist($c);
            $cours[] = $c;
        }

        // ── Compétences (40) ──
        $competenceNames = [
            'Résoudre des équations', 'Analyser un texte', 'Rédiger une synthèse',
            'Expérimenter', 'Calculer des proportions', 'Utiliser un tableur',
            'Lire une carte', 'Argumenter', 'S\'exprimer à l\'oral',
            'Maîtriser le vocabulaire', 'Interpréter des données', 'Modéliser',
            'Programmer', 'Travailler en équipe', 'Rechercher des informations',
        ];
        $niveaux_comp = ['débutant', 'intermédiaire', 'avancé', 'expert'];
        $competences = [];
        for ($i = 0; $i < 40; $i++) {
            $comp = new Competence();
            $comp->setNom($faker->randomElement($competenceNames));
            $comp->setNiveau($faker->randomElement($niveaux_comp));
            $comp->setCours($faker->randomElement($cours));
            $manager->persist($comp);
            $competences[] = $comp;
        }

        // ── Activités (60) — moitié contenu, moitié QCM ──
        $activiteTypes = ['contenu', 'qcm'];
        $contenuTypes = ['video', 'pdf', 'article', 'image'];
        $activites = [];
        $ordre = 1;

        foreach ($cours as $c) {
            $nbActivites = $faker->numberBetween(2, 5);
            for ($j = 0; $j < $nbActivites; $j++) {
                $activite = new Activite();
                $type = $faker->randomElement($activiteTypes);
                $activite->setType($type);
                $activite->setOrdre($ordre++);
                $activite->setCours($c);
                $manager->persist($activite);

                if ($type === 'contenu') {
                    // ── Contenu ──
                    $contenu = new Contenu();
                    $contenu->setType($faker->randomElement($contenuTypes));
                    $contenu->setUrl($faker->url());
                    $contenu->setActivite($activite);
                    $manager->persist($contenu);
                } else {
                    // ── QCM avec Questions & Réponses ──
                    $qcm = new Qcm();
                    $qcm->setGainPts($faker->numberBetween(5, 50));
                    $qcm->setActivite($activite);
                    $manager->persist($qcm);

                    $nbQuestions = $faker->numberBetween(2, 5);
                    for ($q = 0; $q < $nbQuestions; $q++) {
                        $question = new Question();
                        $question->setEnonce($faker->sentence(10) . ' ?');
                        $question->setQcm($qcm);
                        $manager->persist($question);

                        // 4 réponses par question, 1 seule correcte
                        $correctIndex = $faker->numberBetween(0, 3);
                        for ($r = 0; $r < 4; $r++) {
                            $reponse = new Reponse();
                            $reponse->setTexte($faker->sentence(5));
                            $reponse->setIsCorrect($r === $correctIndex);
                            $reponse->setQuestion($question);
                            $manager->persist($reponse);
                        }
                    }
                }

                $activites[] = $activite;
            }
        }

        // ── Progressions (100) ──
        for ($i = 0; $i < 100; $i++) {
            $progression = new Progression();
            $progression->setEleve($faker->randomElement($eleves));
            $progression->setCours($faker->randomElement($cours));
            $progression->setBadge($faker->randomElement($badges));
            $progression->setPercentage($faker->numberBetween(0, 100));
            $manager->persist($progression);
        }

        // ── EleveCompetences (80) ──
        $usedPairs = [];
        for ($i = 0; $i < 80; $i++) {
            $eleve = $faker->randomElement($eleves);
            $competence = $faker->randomElement($competences);
            $pairKey = $eleve->getEmail() . '-' . $competence->getNom() . '-' . spl_object_id($competence);

            if (in_array($pairKey, $usedPairs)) {
                continue;
            }
            $usedPairs[] = $pairKey;

            $ec = new EleveCompetence();
            $ec->setEleve($eleve);
            $ec->setCompetence($competence);
            $manager->persist($ec);
        }

        $manager->flush();
    }

    public function load(ObjectManager $manager): void
    {
        //MatiereFactory::createMany(3);

        //MatiereFactory::createFromBase();
        /*
        BadgeFactory::createFromBase();
        ClasseFactory::createFromBase();

        ProfesseurFactory::createMany(5);
        EleveFactory::createMany(50);

        CoursFactory::createMany(20);
        CompetenceFactory::createMany(40);

        ActiviteFactory::createMany(60);

        ProgressionFactory::createMany(100);
        EleveCompetenceFactory::createMany(80);
        */

        $manager->flush();
    }
}
