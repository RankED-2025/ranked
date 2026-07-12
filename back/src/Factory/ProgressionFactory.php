<?php

namespace App\Factory;

use App\Entity\Progression;
use App\Trait\EntityFactoryHelper;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Progression>
 */
final class ProgressionFactory extends PersistentProxyObjectFactory
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
        return Progression::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'percentage' => self::faker()->numberBetween(0, 100),
            'eleve'      => self::fromLazyFactoryValue(EleveFactory::class),
            'cours'      => self::fromLazyFactoryValue(CoursFactory::class) ,
            'badge'      => self::fromLazyFactoryValue(BadgeFactory::class),
            'classe'     => null,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Progression $progression) {
            // mirrors ProfessorCourseController::assignToClass, where a progression
            // is scoped to the class of the student it was created for
            if ($progression->getClasse() === null && $progression->getEleve()?->getClasse() !== null) {
                $progression->setClasse($progression->getEleve()->getClasse());
            }
        });
    }
}
