<?php

namespace App\Tests\Service;

use App\Dto\RegisterProfesseurRequest;
use App\Repository\UserRepository;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationServiceTest extends TestCase
{
    public function testRegisterProfesseur(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed_password');

        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $jwtManager->method('create')->willReturn('jwt_token');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')->willReturn(null);

        $service = new RegistrationService($em, $passwordHasher, $jwtManager, $userRepository);

        $dto = new RegisterProfesseurRequest(
            name: 'Martin',
            firstname: 'Sophie',
            email: 'sophie.martin@example.com',
            password: 'SecurePass123',
        );

        $result = $service->registerProfesseur($dto);

        $this->assertArrayHasKey('token', $result);
        $this->assertSame('jwt_token', $result['token']);
    }

    public function testRegisterProfesseurThrowsOnDuplicateEmail(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);

        $existingUser = $this->createMock(\App\Entity\User::class);
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')->willReturn($existingUser);

        $service = new RegistrationService($em, $passwordHasher, $jwtManager, $userRepository);

        $dto = new RegisterProfesseurRequest(
            name: 'Martin',
            firstname: 'Sophie',
            email: 'duplicate@example.com',
            password: 'SecurePass123',
        );

        $this->expectException(\DomainException::class);
        $service->registerProfesseur($dto);
    }
}
