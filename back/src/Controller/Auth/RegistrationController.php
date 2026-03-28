<?php

namespace App\Controller\Auth;

use App\Dto\RegisterEleveRequest;
use App\Service\RegistrationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/register', name: 'api_register_')]
class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
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
        } catch (\Throwable $e) {
            $this->logger->error('Registration failed.', [
                'exception' => $e,
                'email' => $dto->email,
                'path' => $request->getPathInfo(),
            ]);

            return $this->json([
                'error' => 'Registration failed. Please try again later.',
            ], 500);
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
