<?php

namespace App\Controller\Auth;

use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
    ) {}

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tokenString = $data['refresh_token'] ?? null;

        if ($tokenString === null) {
            return $this->json(['error' => 'refresh_token is required.'], Response::HTTP_BAD_REQUEST);
        }

        $refreshToken = $this->refreshTokenManager->get($tokenString);

        if ($refreshToken === null) {
            return $this->json(['error' => 'Invalid refresh token.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->refreshTokenManager->delete($refreshToken);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
