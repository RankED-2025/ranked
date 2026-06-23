<?php

namespace App\Twig\Extension;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('crud_url', $this->crudUrl(...)),
        ];
    }

    public function crudUrl(string $controllerShortName, string $action, int|string|null $entityId = null): string
    {
        $generator = $this->adminUrlGenerator
            ->setController('App\\Controller\\Admin\\' . $controllerShortName)
            ->setAction($action);

        if ($entityId !== null) {
            $generator->setEntityId($entityId);
        }

        return $generator->generateUrl();
    }
}
