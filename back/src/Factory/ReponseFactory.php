<?php

namespace App\Factory;

use App\Entity\Reponse;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Reponse>
 */
final class ReponseFactory extends PersistentProxyObjectFactory
{
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
        return Reponse::class;
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
            'isCorrect' => false,
            'texte'     => self::faker()->realTextBetween(20, 50),
            'question'  => LazyValue::new(function () {
                $existing = QuestionFactory::repository()->findAll();

                return count($existing) > 0
                    ? self::faker()->randomElement($existing)
                    : QuestionFactory::new();
            }),
        ];
    }

    public function isCorrect()
    {
        return $this->with([
            'isCorrect' => true,
        ]);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Reponse $reponse): void {})
        ;
    }
}
