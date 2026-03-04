<?php

namespace App\EventListener;

use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_success')]
class AuthenticationSuccessListener
{
    public function __construct(
        private readonly RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
    ) {}

    public function __invoke(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 2592000);
        $this->refreshTokenManager->save($refreshToken);

        $data = $event->getData();
        $data['refresh_token'] = $refreshToken->getRefreshToken();
        $event->setData($data);
    }
}
