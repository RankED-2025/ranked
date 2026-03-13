<?php

namespace App\Controller\Auth;

use App\Entity\Eleve;
use App\Entity\Professeur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class MeController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    ) {}

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'firstname' => $user->getFirstname(),
            'roles' => $user->getRoles(),
            'type' => match (true) {
                $user instanceof Eleve => 'eleve',
                $user instanceof Professeur => 'professeur',
                default => 'unknown',
            },
        ];

        if ($user instanceof Eleve) {
            $data['classe'] = $user->getClasse()?->getId();
        }

        return $this->json($data);
    }
}
