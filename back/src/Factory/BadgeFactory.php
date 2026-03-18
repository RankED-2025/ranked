<?php

namespace App\Factory;

use App\Entity\Badge;
use App\Trait\EntityFactoryHelper;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Badge>
 */
final class BadgeFactory extends PersistentProxyObjectFactory
{
    use EntityFactoryHelper;

    public const BASE_BADGE_DATA = [
        ['type' => 'bronze',   'label' => 'Débutant'],
        ['type' => 'argent',   'label' => 'Intermédiaire'],
        ['type' => 'or',       'label' => 'Avancé'],
        ['type' => 'platine',  'label' => 'Expert'],
        ['type' => 'diamant',  'label' => 'Maître'],
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
        return Badge::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'label' => self::faker()->word(),
            'type'  => self::faker()->word(),
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
