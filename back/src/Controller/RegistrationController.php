<?php

namespace App\Controller;

use App\Dto\RegisterEleveRequest;
use App\Dto\RegisterProfesseurRequest;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/register', name: 'api_register_')]
class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/eleve', name: 'eleve', methods: ['POST'])]
    public function registerEleve(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new RegisterEleveRequest(
            name: $data['name'] ?? '',
            firstname: $data['firstname'] ?? '',
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => $this->formatErrors($errors)], 422);
        }

        try {
            $result = $this->registrationService->registerEleve($dto);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        }

        return $this->json($result, 201);
    }

    #[Route('/professeur', name: 'professeur', methods: ['POST'])]
    public function registerProfesseur(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new RegisterProfesseurRequest(
            name: $data['name'] ?? '',
            firstname: $data['firstname'] ?? '',
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => $this->formatErrors($errors)], 422);
        }

        try {
            $result = $this->registrationService->registerProfesseur($dto);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        }

        return $this->json($result, 201);
    }

    private function formatErrors(\Symfony\Component\Validator\ConstraintViolationListInterface $errors): array
    {
        $formatted = [];
        foreach ($errors as $error) {
            $formatted[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }
        return $formatted;
    }
}
