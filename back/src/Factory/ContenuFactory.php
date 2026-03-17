<?php

namespace App\Factory;

use App\Entity\Contenu;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Contenu>
 */
final class ContenuFactory extends PersistentProxyObjectFactory
{
    public const BASE_CONTENU_TYPES = [
        'video', 'pdf', 'article', 'image'
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
        return Contenu::class;
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
            'type'     => self::faker()->randomElement(self::BASE_CONTENU_TYPES),
            'url'      => self::faker()->url(),
            'activite' => LazyValue::new(function () {
                $existing = ActiviteFactory::repository()->findAll();

                return count($existing) > 0
                    ? self::faker()->randomElement($existing)
                    : ActiviteFactory::new();
            }),
        ];
    }

    /**
     * Creates the Contenu entities from the base data
     * @return Contenu[]
     */
    public static function createFromBase(): array
    {
        return self::new()
            ->sequence(
                array_map(
                    fn($c) => ['type' => $c],
                    self::BASE_CONTENU_TYPES
                )
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
            // ->afterInstantiate(function(Contenu $contenu): void {})
        ;
    }
}
