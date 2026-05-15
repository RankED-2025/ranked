<?php

namespace App\Factory;

use App\Entity\Eleve;
use App\Trait\EntityFactoryHelper;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Eleve>
 */
final class EleveFactory extends PersistentProxyObjectFactory
{
    use EntityFactoryHelper;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        //
    }

    #[\Override]
    public static function class(): string
    {
        return Eleve::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'name'      => self::faker()->lastName(),
            'firstname' => self::faker()->firstName(),
            'email'     => self::faker()->unique()->safeEmail(),
            'password'  => 'password',
            'roles'     => ['ROLE_ELEVE'],
            'classe'    => null,
            'createdAt' => new \DateTimeImmutable(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Eleve $eleve) {
            $eleve->setPassword(
                $this->passwordHasher->hashPassword($eleve, $eleve->getPassword())
            );
        });
    }
}
