<?php

namespace App\Factory;

use App\Entity\Activite;
use http\Exception\InvalidArgumentException;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Activite>
 */
final class ActiviteFactory extends PersistentProxyObjectFactory
{
    public const BASE_ACTIVITE_TYPES = [
        'contenu', 'qcm'
    ];

    private static int $currentOrder = 1;

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
        $this->incrementCurrentOrder(1);

        return [
            'ordre' => $this->getCurrentOrder(),
            'type' => self::faker()->randomElement(self::BASE_ACTIVITE_TYPES),
        ];
    }

    /**
     * Creates the Activite entities from the base data
     * @return Activite[]
     */
    public static function createFromBase(): array
    {
        $order = 1;

        return self::new()
            ->sequence(
                array_map(fn($c) => [
                    'ordre' => $order++,
                    'type' => $c
                ], self::BASE_ACTIVITE_TYPES)
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
            // ->afterInstantiate(function(Activite $activite): void {})
        ;
    }

    // -----------------------------------------------

    public static function getCurrentOrder(): int
    {
        return self::$currentOrder;
    }

    public static function incrementCurrentOrder(int $plus = 1): void
    {
        if( $plus <= 0 ) {
            throw new InvalidArgumentException('Cannot call "incrementCurrentOrder" with a value lower than 1');
        }

        self::$currentOrder += $plus;
    }

    public static function resetCurrentOrder()
    {
        self::$currentOrder = 1;
    }
}
