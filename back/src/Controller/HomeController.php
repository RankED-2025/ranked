<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if ($request->getContentTypeFormat() === 'json') {
            return $this->json([
                'name' => 'Ranked API',
                'status' => 'ok',
            ]);
        }

        /** @var User|null $user */
        $user = $this->getUser();

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'isAdmin' => $user !== null && in_array('ROLE_ADMIN', $user->getRoles(), true),
        ]);
    }
}
