<?php

namespace App\Factory;

use App\Entity\Qcm;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Qcm>
 */
final class QcmFactory extends PersistentProxyObjectFactory
{
    private bool $withQuestions = false;

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
        return Qcm::class;
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
            'gainPts' => self::faker()->numberBetween(5, 50),
            'activite' => LazyValue::new(function () {
                $existing = ActiviteFactory::repository()->findAll();

                return count($existing) > 0
                    ? self::faker()->randomElement($existing)
                    : ActiviteFactory::new();
            }),
        ];
    }

    public function withQuestions(): static
    {
        $this->withQuestions = true;
        return $this;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Qcm $qcm): void {
                if ($this->withQuestions) {
                    QuestionFactory::createMany(
                        self::faker()->numberBetween(3, 10),
                        ['qcm' => $qcm]
                    );
                }
            });
    }
}
