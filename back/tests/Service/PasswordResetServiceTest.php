<?php

namespace App\Tests\Service;

use App\Dto\PasswordResetConfirmDto;
use App\Dto\PasswordResetRequestDto;
use App\Entity\Eleve;
use App\Entity\PasswordResetToken;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use App\Service\PasswordResetService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Twig\Environment;

class PasswordResetServiceTest extends TestCase
{
    private function createUser(): Eleve
    {
        $eleve = new Eleve();
        $eleve->setEmail('test@example.com');
        $eleve->setName('Doe');
        $eleve->setFirstname('John');
        $eleve->setPassword('hashed');
        return $eleve;
    }

    public static function frontendUrlProvider(): array
    {
        return [
            'no trailing slash' => ['https://example.com', 'https://example.com/reset-password?token='],
            'single trailing slash' => ['https://example.com/', 'https://example.com/reset-password?token='],
            'multiple trailing slashes' => ['https://example.com///', 'https://example.com/reset-password?token='],
            'sub-path with trailing slash' => ['https://example.com/app/', 'https://example.com/app/reset-password?token='],
        ];
    }

    #[DataProvider('frontendUrlProvider')]
    public function testRequestResetBuildsResetUrlWithoutDoubleSlash(string $frontendUrl, string $expectedPrefix): void
    {
        $user = $this->createUser();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')->willReturn($user);

        $tokenRepository = $this->createMock(PasswordResetTokenRepository::class);
        $tokenRepository->expects($this->once())->method('invalidateAllForUser')->with($user);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send');

        $capturedResetUrl = null;
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturnCallback(
            function (string $template, array $context) use (&$capturedResetUrl): string {
                $capturedResetUrl = $context['reset_url'];
                return '<html></html>';
            }
        );

        $service = new PasswordResetService(
            $em,
            $userRepository,
            $tokenRepository,
            $passwordHasher,
            $mailer,
            $twig,
            $frontendUrl,
            'no-reply@example.com',
        );

        $service->requestReset(new PasswordResetRequestDto(email: 'test@example.com'));

        $this->assertStringStartsWith($expectedPrefix, $capturedResetUrl);
        $this->assertStringNotContainsString('//reset-password', $capturedResetUrl);
    }

    public function testRequestResetDoesNothingWhenUserNotFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findOneBy')->willReturn(null);

        $tokenRepository = $this->createMock(PasswordResetTokenRepository::class);
        $tokenRepository->expects($this->never())->method('invalidateAllForUser');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');
        $twig = $this->createMock(Environment::class);

        $service = new PasswordResetService(
            $em,
            $userRepository,
            $tokenRepository,
            $passwordHasher,
            $mailer,
            $twig,
            'https://example.com',
            'no-reply@example.com',
        );

        $service->requestReset(new PasswordResetRequestDto(email: 'unknown@example.com'));
    }

    public static function confirmResetPasswordProvider(): array
    {
        $repeatedA = str_repeat('a', 72);

        return [
            'simple password' => ['NewSecurePass123', 'hashed_NewSecurePass123'],
            'password with special chars' => ['C0mpl3x!@#Pass', 'hashed_C0mpl3x!@#Pass'],
            'long password' => [$repeatedA, 'hashed_' . $repeatedA],
        ];
    }

    #[DataProvider('confirmResetPasswordProvider')]
    public function testConfirmResetHashesPasswordAndMarksTokenAsUsed(string $plainPassword, string $expectedHash): void
    {
        $user = $this->createUser();
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $token = new PasswordResetToken($user, $expiresAt);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $userRepository = $this->createMock(UserRepository::class);

        $tokenRepository = $this->createMock(PasswordResetTokenRepository::class);
        $tokenRepository->method('findValidTokenByValue')->with($token->getToken())->willReturn($token);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->with($user, $plainPassword)->willReturn($expectedHash);

        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);

        $service = new PasswordResetService(
            $em,
            $userRepository,
            $tokenRepository,
            $passwordHasher,
            $mailer,
            $twig,
            'https://example.com',
            'no-reply@example.com',
        );

        $service->confirmReset(new PasswordResetConfirmDto(token: $token->getToken(), password: $plainPassword));

        $this->assertSame($expectedHash, $user->getPassword());
        $this->assertTrue($token->isUsed());
    }

    public function testConfirmResetThrowsWhenTokenIsInvalidOrExpired(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $userRepository = $this->createMock(UserRepository::class);

        $tokenRepository = $this->createMock(PasswordResetTokenRepository::class);
        $tokenRepository->method('findValidTokenByValue')->willReturn(null);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects($this->never())->method('hashPassword');

        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);

        $service = new PasswordResetService(
            $em,
            $userRepository,
            $tokenRepository,
            $passwordHasher,
            $mailer,
            $twig,
            'https://example.com',
            'no-reply@example.com',
        );

        $this->expectException(\DomainException::class);

        $service->confirmReset(new PasswordResetConfirmDto(token: 'invalid-token', password: 'NewSecurePass123'));
    }
}
