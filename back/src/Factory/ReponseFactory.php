<?php

namespace App\Factory;

use App\Entity\Reponse;
use App\Trait\EntityFactoryHelper;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Reponse>
 */
final class ReponseFactory extends PersistentProxyObjectFactory
{
    use EntityFactoryHelper;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
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
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'isCorrect' => false,
            'texte'     => self::faker()->realTextBetween(20, 50),
            'question'  => self::fromLazyFactoryValue(QuestionFactory::class),
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
        return $this;
    }
}
