<?php

namespace App\Factory;

use App\Entity\Matiere;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Matiere>
 */
final class MatiereFactory extends PersistentProxyObjectFactory
{
    public const BASE_MATIERES = [
        'Mathématiques',
        'Français',
        'Histoire-Géographie',
        'Physique-Chimie',
        'SVT',
        'Anglais',
        'Espagnol',
        'Technologie'
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
        return Matiere::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     * @TODO: Cours Collection
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'libelle' => self::faker()->word(),
        ];
    }

    /**
     * Creates some Matieres entities from the base ones.
     * @return Matiere[]
     */
    public static function createFromBase(): array
    {
        return self::new()
            ->sequence(
                array_map(fn($m) => ['libelle' => $m], self::BASE_MATIERES)
            )
            ->create();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Matiere $matiere): void {})
        ;
    }
}
