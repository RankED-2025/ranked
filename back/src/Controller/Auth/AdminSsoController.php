<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Service\AdminSsoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdminSsoController extends AbstractController
{
    public function __construct(
        private readonly AdminSsoService $adminSsoService,
    ) {}

    #[Route('/api/admin/sso', name: 'api_admin_sso', methods: ['POST'])]
    public function createSsoLink(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $user */
        $user = $this->getUser();

        $token = $this->adminSsoService->createSsoUrl($user);

        $url = $this->generateUrl(
            'admin_sso_consume',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return $this->json(['url' => $url]);
    }
}
