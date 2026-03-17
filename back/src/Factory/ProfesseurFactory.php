<?php

namespace App\Factory;

use App\Entity\Professeur;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Professeur>
 */
final class ProfesseurFactory extends PersistentProxyObjectFactory
{
    private bool $withClasses = false;
    private bool $withCours   = false;

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
     *
     * @todo add your default values here
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
        ];
    }

    public function withClasses(): static
    {
        $this->withClasses = true;
        return $this;
    }

    public function withCours(): static
    {
        $this->withCours = true;
        return $this;
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

            if ($this->withClasses) {
                ClasseFactory::createMany(
                    self::faker()->numberBetween(1, 5),
                    ['professeur' => $professeur]
                );
            }

            if ($this->withCours) {
                CoursFactory::createMany(
                    self::faker()->numberBetween(1, 10),
                    ['professeur' => $professeur]
                );
            }
        });
    }
}
