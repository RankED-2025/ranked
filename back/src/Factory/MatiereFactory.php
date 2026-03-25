<?php

namespace App\Factory;

use App\Entity\Matiere;
use App\Trait\EntityFactoryHelper;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Matiere>
 */
final class MatiereFactory extends PersistentProxyObjectFactory
{
    use EntityFactoryHelper;

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
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'libelle' => self::faker()->randomElement(self::BASE_MATIERES),
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

    /**
     * Creates data from the base data.
     * @param array $with
     * @return Matiere[]
     */
    public static function createFromBase(array $with = []): array
    {
        return self::createSequence(
            array_map(
                fn($libelle) => [
                    'libelle' => $libelle,
                    ...$with
                ],
                self::BASE_MATIERES
            )
        );
    }
}
