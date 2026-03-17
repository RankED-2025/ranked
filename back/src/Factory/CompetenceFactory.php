<?php

namespace App\Factory;

use App\Entity\Competence;
use Zenstruck\Foundry\LazyValue;
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
            'niveau' => self::faker()->randomElement(self::BASE_COMPETENCE_NIVEAU),
            'nom' => self::faker()->randomElement(self::BASE_COMPETENCES),
            'cours'  => LazyValue::new(function () {
                $existing = CoursFactory::repository()->findAll();

                return count($existing) > 0
                    ? self::faker()->randomElement($existing)
                    : CoursFactory::new();
            }),
        ];
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
