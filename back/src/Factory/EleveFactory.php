<?php

namespace App\Factory;

use App\Entity\Eleve;
use App\Entity\Professeur;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Eleve>
 */
final class EleveFactory extends PersistentProxyObjectFactory
{
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
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->email(),
            'firstname' => self::faker()->firstName(),
            'name' => self::faker()->lastName(),
            'password' => 'password',
            'roles' => ['ROLE_ELEVE'],
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
