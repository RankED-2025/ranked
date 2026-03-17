<?php

namespace App\Factory;

use App\Entity\Cours;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Cours>
 */
final class CoursFactory extends PersistentProxyObjectFactory
{
    private bool $withActivites = false;
    private bool $withCompetences = false;
    private bool $withProgressions = false;

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
        return Cours::class;
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
            'professeur' => LazyValue::new(function () {
                $existing = ProfesseurFactory::repository()->findAll();

                return count($existing) > 0
                    ? self::faker()->randomElement($existing)
                    : ProfesseurFactory::new();
            }),
            'matiere' => LazyValue::new(function () {
                $existing = MatiereFactory::repository()->findAll();

                return count($existing) > 0
                    ? self::faker()->randomElement($existing)
                    : MatiereFactory::new();
            }),
        ];
    }

    public function withActivites(): static
    {
        $clone = clone $this;
        $clone->withActivites = true;
        return $clone;
    }

    public function withCompetences(): static
    {
        $clone = clone $this;
        $clone->withCompetences = true;
        return $clone;
    }

    public function withProgressions(): static
    {
        $clone = clone $this;
        $clone->withProgressions = true;
        return $clone;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Cours $cours) {
                if ($this->withActivites) {
                    ActiviteFactory::createMany(self::faker()->numberBetween(1, 5), ['cours' => $cours]);
                }
                if ($this->withCompetences) {
                    CompetenceFactory::createMany(self::faker()->numberBetween(1, 3), ['cours' => $cours]);
                }
                if ($this->withProgressions) {
                    ProgressionFactory::createMany(self::faker()->numberBetween(1, 3), ['cours' => $cours]);
                }
            });
    }
}
