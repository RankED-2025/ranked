<?php

namespace App\Factory;

use App\Entity\Competence;
use App\Trait\EntityFactoryHelper;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Competence>
 */
final class CompetenceFactory extends PersistentProxyObjectFactory
{
    use EntityFactoryHelper;

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
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'niveau' => self::faker()->randomElement(self::BASE_COMPETENCE_NIVEAU),
            'nom'    => self::faker()->randomElement(self::BASE_COMPETENCES),
            'cours'  => self::fromLazyFactoryValue(CoursFactory::class),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
