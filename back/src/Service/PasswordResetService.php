<?php

namespace App\Service;

use App\Dto\PasswordResetConfirmDto;
use App\Dto\PasswordResetRequestDto;
use App\Entity\PasswordResetToken;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Twig\Environment;

class PasswordResetService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly PasswordResetTokenRepository $tokenRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly string $frontendUrl,
        private readonly string $fromEmailString,
    ) {}

    public function requestReset(PasswordResetRequestDto $dto): void
    {
        $user = $this->userRepository->findOneBy(['email' => $dto->email]);
        if ($user === null) {
            return;
        }

        $this->tokenRepository->invalidateAllForUser($user);

        $expiresAt = new \DateTimeImmutable('+3600 seconds');
        $token = new PasswordResetToken($user, $expiresAt);
        $this->em->persist($token);
        $this->em->flush();

        $frontEndUrl = $this->frontendUrl;

        if( str_ends_with("/", $frontEndUrl) ) {
            $frontEndUrl = substr($frontEndUrl, 0, -1);
        }

        $resetUrl = $frontEndUrl . '/reset-password?token=' . $token->getToken();

        $htmlBody = $this->twig->render('email/password_reset.html.twig', [
            'user' => $user,
            'token' => $token->getToken(),
            'expires_at' => $expiresAt,
            'reset_url' => $resetUrl,
        ]);

        $textBody = $this->twig->render('email/password_reset.txt.twig', [
            'user' => $user,
            'token' => $token->getToken(),
            'expires_at' => $expiresAt,
            'reset_url' => $resetUrl,
        ]);

        $email = (new Email())
            ->from($this->fromEmailString)
            ->to($user->getEmail())
            ->subject('Reset your password')
            ->html($htmlBody)
            ->text($textBody);

        $this->mailer->send($email);
    }

    public function confirmReset(PasswordResetConfirmDto $dto): void
    {
        $token = $this->tokenRepository->findValidTokenByValue($dto->token);
        if ($token === null) {
            throw new \DomainException('Invalid or expired password reset token.');
        }

        $user = $token->getUser();
        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));
        $token->markAsUsed();

        $this->em->flush();
    }
}
