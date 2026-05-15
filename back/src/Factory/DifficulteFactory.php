<?php

namespace App\Factory;

use App\Entity\Difficulte;
use App\Trait\EntityFactoryHelper;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Difficulte>
 */
final class DifficulteFactory extends PersistentProxyObjectFactory
{
    use EntityFactoryHelper;

    public const BASE_DIFFICULTE_DATA = [
        ['label' => 'Facile'],
        ['label' => 'Moyen'],
        ['label' => 'Avancé'],
        ['label' => 'Expert'],
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
        return Difficulte::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'label' => self::faker()->word(),
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
     * @return Difficulte[]
     */
    public static function createFromBase(array $with = []): array
    {
        return self::createSequence(
            array_map(
                fn($data) => [
                    'label' => $data["label"],
                    ...$with
                ],
                self::BASE_DIFFICULTE_DATA
            )
        );
    }
}
