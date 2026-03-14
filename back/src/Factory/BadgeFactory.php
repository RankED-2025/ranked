<?php

namespace App\Factory;

use App\Entity\Badge;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Badge>
 */
final class BadgeFactory extends PersistentProxyObjectFactory
{
    public const BASE_BADGE_DATA = [
        ['type' => 'bronze',   'label' => 'Débutant'],
        ['type' => 'argent',   'label' => 'Intermédiaire'],
        ['type' => 'or',       'label' => 'Avancé'],
        ['type' => 'platine',  'label' => 'Expert'],
        ['type' => 'diamant',  'label' => 'Maître'],
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
        return Badge::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo Put progressions collection
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'label' => self::faker()->text(255),
            'type' => self::faker()->text(255),
        ];
    }

    /**
     * Creates the Badges from the base data
     * @return Badge[]
     */
    public static function createFromBase(): array
    {
        return self::new()
            ->sequence(self::BASE_BADGE_DATA)
            ->create();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Badge $badge): void {})
        ;
    }
}
