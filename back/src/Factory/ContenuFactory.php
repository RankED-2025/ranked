<?php

namespace App\Factory;

use App\Entity\Contenu;
use App\Trait\EntityFactoryHelper;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Contenu>
 */
final class ContenuFactory extends PersistentProxyObjectFactory
{
    use EntityFactoryHelper;

    public const BASE_CONTENU_TYPES = [
        'video', 'pdf', 'article', 'image'
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
        return Contenu::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'type'     => self::faker()->randomElement(self::BASE_CONTENU_TYPES),
            'url'      => self::faker()->url(),
            'activite' => self::fromLazyFactoryValue(ActiviteFactory::class),
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
