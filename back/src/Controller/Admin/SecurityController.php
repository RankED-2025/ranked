<?php

namespace App\Controller\Admin;

use App\Service\AdminSsoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'csrf_token_intention' => 'authenticate',
        ]);
    }

    #[Route('/logout', name: 'admin_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method is intercepted by the firewall logout listener.');
    }

    #[Route('/admin/sso/{token}', name: 'admin_sso_consume', methods: ['GET'])]
    public function ssoConsume(string $token, AdminSsoService $adminSsoService, Security $security): Response
    {
        try {
            $user = $adminSsoService->consumeToken($token);
        } catch (\DomainException) {
            return $this->redirectToRoute('admin_login');
        }

        $security->login($user, null, 'admin');

        return $this->redirectToRoute('admin');
    }
}
