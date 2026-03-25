<?php

namespace App\Service;

use App\Dto\RegisterEleveRequest;
use App\Dto\RegisterProfesseurRequest;
use App\Entity\Eleve;
use App\Entity\Professeur;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly UserRepository $userRepository,
    ) {}

    public function registerEleve(RegisterEleveRequest $dto): array
    {
        $this->assertEmailUnique($dto->email);

        $eleve = new Eleve();
        $this->hydrateUser($eleve, $dto->name, $dto->firstname, $dto->email, $dto->password);
        $eleve->setRoles(['ROLE_ELEVE']);

        $this->em->persist($eleve);
        $this->em->flush();

        return ['token' => $this->jwtManager->create($eleve)];
    }

    public function registerProfesseur(RegisterProfesseurRequest $dto): array
    {
        $this->assertEmailUnique($dto->email);

        $professeur = new Professeur();
        $this->hydrateUser($professeur, $dto->name, $dto->firstname, $dto->email, $dto->password);
        $professeur->setRoles(['ROLE_PROFESSEUR']);

        $this->em->persist($professeur);
        $this->em->flush();

        return ['token' => $this->jwtManager->create($professeur)];
    }

    private function assertEmailUnique(string $email): void
    {
        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            throw new \DomainException(sprintf('Email "%s" is already registered.', $email));
        }
    }

    private function hydrateUser(User $user, string $name, string $firstname, string $email, string $plainPassword): void
    {
        $user->setName($name);
        $user->setFirstname($firstname);
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
    }
}
