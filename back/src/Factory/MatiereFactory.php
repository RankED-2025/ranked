<?php

namespace App\Factory;

use App\Entity\Matiere;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\LazyValue;
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
}
