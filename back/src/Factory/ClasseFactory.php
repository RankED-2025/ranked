<?php

namespace App\Factory;

use App\Entity\Classe;
use Zenstruck\Foundry\LazyValue;
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

    private bool $withEleves = false;

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
        $randomDegree = self::faker()->randomElement(self::BASE_CLASSES);
        $randomClasse = self::faker()->randomElement(self::BASE_DEGREE);

        return [
            'nom' => $randomDegree . " " . $randomClasse,
            'professeur' => LazyValue::new(function () {
                $existing = ProfesseurFactory::repository()->findAll();

                return count($existing) > 0
                    ? self::faker()->randomElement($existing)
                    : ProfesseurFactory::new();
            }),
        ];
    }

    public function withEleves(): static
    {
        $clone = clone $this;
        $clone->withEleves = true;
        return $clone;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Classe $classe): void {
                if ($this->withEleves) {
                    EleveFactory::createMany(
                        self::faker()->numberBetween(15, 30),
                        ['classe' => $classe]
                    );
                }
            });
    }
}
