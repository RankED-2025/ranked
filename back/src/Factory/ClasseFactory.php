<?php

namespace App\Factory;

use App\Entity\Classe;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Classe>
 */
final class ClasseFactory extends PersistentProxyObjectFactory
{
    public const BASE_DEGREE = [
        '6ème',
        '5ème',
        '4ème',
        '3ème'
    ];

    public const BASE_CLASSES = [
        'A',
        'B',
        'C',
        'D',
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
        return Classe::class;
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
            'nom' => self::faker()->word() . " " . self::faker()->randomLetter(),
        ];
    }

    /**
     * Creates the Classes entities from the base data
     * @return Classe[]
     */
    public static function createFromBase(array $attributes = []): array
    {
        $classesStrings = [];

        foreach (self::BASE_DEGREE as $degree) {
            foreach (self::BASE_CLASSES as $class) {
                $classesStrings[] = $degree . " " . $class;
            }
        }

        return self::new()
            ->with($attributes)
            ->sequence(
                array_map(fn($c) => [ 'nom' => $c ], $classesStrings)
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
            // ->afterInstantiate(function(Classe $classe): void {})
        ;
    }
}
