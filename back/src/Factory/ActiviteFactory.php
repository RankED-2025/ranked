<?php

namespace App\Factory;

use App\Entity\Activite;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Activite>
 */
final class ActiviteFactory extends PersistentProxyObjectFactory
{
    public const BASE_ACTIVITE_TYPES = [
        'contenu', 'qcm'
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
        return Activite::class;
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
            'ordre' => self::faker()->numberBetween(1, 20),
            'type' => self::faker()->randomElement(self::BASE_ACTIVITE_TYPES),
            'cours' => LazyValue::new(function () {
                $existing = CoursFactory::repository()->findAll();

                return count($existing) > 0
                    ? self::faker()->randomElement($existing)
                    : CoursFactory::new();
            }),
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
        return $this
            // ->afterInstantiate(function(Activite $activite): void {})
        ;
    }

    // -----------------------------------------------

    public function withContenu(): static
    {
        $this->with(['contenu' => ContenuFactory::new()]);
        return $this;
    }

    public function withQcm(): static
    {
        $this->with(['qcm' => QcmFactory::new()]);
        return $this;
    }
}
