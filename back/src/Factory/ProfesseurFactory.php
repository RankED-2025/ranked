<?php

namespace App\Factory;

use App\Entity\Professeur;
use App\Trait\EntityFactoryHelper;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Professeur>
 */
final class ProfesseurFactory extends PersistentProxyObjectFactory
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
        return Professeur::class;
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
            'roles'     => ['ROLE_PROFESSEUR'],
            'createdAt' => new \DateTimeImmutable(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Professeur $professeur) {
            $professeur->setPassword(
                $this->passwordHasher->hashPassword($professeur, $professeur->getPassword())
            );
        });
    }
}
