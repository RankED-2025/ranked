<?php

namespace App\Factory;

use App\Entity\EleveCompetence;
use App\Trait\EntityFactoryHelper;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<EleveCompetence>
 */
final class EleveCompetenceFactory extends PersistentProxyObjectFactory
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
        return EleveCompetence::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'eleve'      => self::fromLazyFactoryValue(EleveFactory::class),
            'competence' => self::fromLazyFactoryValue(CompetenceFactory::class),
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
}
