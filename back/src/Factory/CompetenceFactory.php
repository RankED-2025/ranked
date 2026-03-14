<?php

namespace App\Factory;

use App\Entity\Competence;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Competence>
 */
final class CompetenceFactory extends PersistentProxyObjectFactory
{
    public const BASE_COMPETENCES = [
        'Résoudre des équations', 'Analyser un texte', 'Rédiger une synthèse',
        'Expérimenter', 'Calculer des proportions', 'Utiliser un tableur',
        'Lire une carte', 'Argumenter', "S'exprimer à l'oral",
        'Maîtriser le vocabulaire', 'Interpréter des données', 'Modéliser',
        'Programmer', 'Travailler en équipe', 'Rechercher des informations',
    ];

    public const BASE_COMPETENCE_NIVEAU = [
        'débutant', 'intermédiaire', 'avancé', 'expert'
    ];

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Competence::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'niveau' => self::faker()->word(),
            'nom' => self::faker()->words(self::faker()->numberBetween(1, 3), asText: true),
        ];
    }

    /**
     * Creates the Competence entities from the base data
     * @return Competence[]
     */
    public static function createFromBase(): array
    {
        $competenceList = [];

        foreach (self::BASE_COMPETENCES as $competence) {
            foreach (self::BASE_COMPETENCE_NIVEAU as $baseNiveau) {
                $competenceList[] = [
                    'niveau' => $baseNiveau,
                    'nom' => $competence
                ];
            }
        }

        return self::new()
            ->sequence($competenceList)
            ->create();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Competence $competence): void {})
        ;
    }
}
