<?php

namespace App\Service;

use App\Entity\AdminSsoToken;
use App\Entity\User;
use App\Repository\AdminSsoTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminSsoService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdminSsoTokenRepository $tokenRepository,
    ) {}

    public function createSsoUrl(User $user): string
    {
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw new \DomainException('User is not an administrator.');
        }

        $this->tokenRepository->invalidateAllForUser($user);

        $expiresAt = new \DateTimeImmutable('+60 seconds');
        $token = new AdminSsoToken($user, $expiresAt);
        $this->em->persist($token);
        $this->em->flush();

        return $token->getToken();
    }

    public function consumeToken(string $token): User
    {
        $ssoToken = $this->tokenRepository->findValidTokenByValue($token);
        if ($ssoToken === null) {
            throw new \DomainException('Invalid or expired admin SSO token.');
        }

        $user = $ssoToken->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw new \DomainException('User is not an administrator.');
        }

        $ssoToken->markAsUsed();
        $this->em->flush();

        return $user;
    }
}
