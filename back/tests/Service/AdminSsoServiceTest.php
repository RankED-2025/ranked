<?php

namespace App\Tests\Service;

use App\Entity\AdminSsoToken;
use App\Entity\Eleve;
use App\Repository\AdminSsoTokenRepository;
use App\Service\AdminSsoService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdminSsoServiceTest extends TestCase
{
    private function createUser(array $roles): Eleve
    {
        $eleve = new Eleve();
        $eleve->setEmail('test@example.com');
        $eleve->setName('Doe');
        $eleve->setFirstname('John');
        $eleve->setPassword('hashed');
        $eleve->setRoles($roles);
        return $eleve;
    }

    public static function nonAdminRolesProvider(): array
    {
        return [
            'no extra roles' => [[]],
            'eleve role' => [['ROLE_ELEVE']],
            'professeur role' => [['ROLE_PROFESSEUR']],
        ];
    }

    #[DataProvider('nonAdminRolesProvider')]
    public function testCreateSsoUrlThrowsForNonAdmin(array $roles): void
    {
        $user = $this->createUser($roles);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');

        $tokenRepository = $this->createMock(AdminSsoTokenRepository::class);
        $tokenRepository->expects($this->never())->method('invalidateAllForUser');

        $service = new AdminSsoService($em, $tokenRepository);

        $this->expectException(\DomainException::class);
        $service->createSsoUrl($user);
    }

    public static function adminRolesProvider(): array
    {
        return [
            'admin only' => [['ROLE_ADMIN']],
            'admin and professeur' => [['ROLE_ADMIN', 'ROLE_PROFESSEUR']],
        ];
    }

    #[DataProvider('adminRolesProvider')]
    public function testCreateSsoUrlSucceedsForAdmin(array $roles): void
    {
        $user = $this->createUser($roles);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $tokenRepository = $this->createMock(AdminSsoTokenRepository::class);
        $tokenRepository->expects($this->once())->method('invalidateAllForUser')->with($user);

        $service = new AdminSsoService($em, $tokenRepository);

        $token = $service->createSsoUrl($user);

        $this->assertSame(64, strlen($token));
    }

    public function testConsumeTokenReturnsUserAndMarksTokenAsUsed(): void
    {
        $user = $this->createUser(['ROLE_ADMIN']);
        $ssoToken = new AdminSsoToken($user, new \DateTimeImmutable('+60 seconds'));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $tokenRepository = $this->createMock(AdminSsoTokenRepository::class);
        $tokenRepository->method('findValidTokenByValue')->with($ssoToken->getToken())->willReturn($ssoToken);

        $service = new AdminSsoService($em, $tokenRepository);

        $result = $service->consumeToken($ssoToken->getToken());

        $this->assertSame($user, $result);
        $this->assertTrue($ssoToken->isUsed());
    }

    public function testConsumeTokenThrowsWhenTokenNotFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $tokenRepository = $this->createMock(AdminSsoTokenRepository::class);
        $tokenRepository->method('findValidTokenByValue')->willReturn(null);

        $service = new AdminSsoService($em, $tokenRepository);

        $this->expectException(\DomainException::class);
        $service->consumeToken('invalid-token');
    }

    public function testConsumeTokenThrowsWhenUserIsNoLongerAdmin(): void
    {
        $user = $this->createUser(['ROLE_PROFESSEUR']);
        $ssoToken = new AdminSsoToken($user, new \DateTimeImmutable('+60 seconds'));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $tokenRepository = $this->createMock(AdminSsoTokenRepository::class);
        $tokenRepository->method('findValidTokenByValue')->willReturn($ssoToken);

        $service = new AdminSsoService($em, $tokenRepository);

        $this->expectException(\DomainException::class);
        $service->consumeToken($ssoToken->getToken());
    }
}
