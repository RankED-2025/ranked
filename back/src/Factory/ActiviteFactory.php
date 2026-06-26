<?php

namespace App\Factory;

use App\Entity\Activite;
use App\Trait\EntityFactoryHelper;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Activite>
 */
final class ActiviteFactory extends PersistentProxyObjectFactory
{
    use EntityFactoryHelper;

    public const BASE_ACTIVITE_TYPES = [
        'contenu', 'qcm'
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
        return Activite::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'ordre'   => self::faker()->numberBetween(1, 20),
            'type'    => self::faker()->randomElement(self::BASE_ACTIVITE_TYPES),
            'cours'   => self::fromLazyFactoryValue(CoursFactory::class),
            'contenu' => null,
            'qcm'     => null,
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

    // -----------------------------------------------

    public function withContenu(): static
    {
        return $this->with(['type' => 'contenu', 'contenu' => ContenuFactory::new(), 'qcm' => null]);
    }

    public function withQcm(): static
    {
        return $this->with(['type' => 'qcm', 'qcm' => QcmFactory::new(), 'contenu' => null]);
    }
}
