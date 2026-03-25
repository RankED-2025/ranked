<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 15)]
class RateLimitListener
{
    public function __construct(
        private readonly RateLimiterFactory $authLoginLimiter,
        private readonly RateLimiterFactory $apiRegisterLimiter,
        private readonly RateLimiterFactory $apiPasswordResetLimiter,
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->getMethod() !== 'POST') {
            return;
        }

        $factory = $this->resolveLimiter($request);
        if ($factory === null) {
            return;
        }

        $limiter = $factory->create($request->getClientIp() ?? '0.0.0.0');
        if (!$limiter->consume(1)->isAccepted()) {
            $event->setResponse(new JsonResponse(
                ['error' => 'Too many requests. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS
            ));
        }
    }

    private function resolveLimiter(Request $request): ?RateLimiterFactory
    {
        $path = $request->getPathInfo();
        if ($path === '/api/login') return $this->authLoginLimiter;
        if (str_starts_with($path, '/api/register/')) return $this->apiRegisterLimiter;
        if ($path === '/api/password-reset/request') return $this->apiPasswordResetLimiter;
        return null;
    }
}
