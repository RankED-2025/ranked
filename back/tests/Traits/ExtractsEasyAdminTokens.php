<?php

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Extracts CSRF tokens from EasyAdmin pages.
 * Requires $this->client to be a KernelBrowser (e.g. via AbstractCrudTestCase).
 */
trait ExtractsEasyAdminTokens
{
    /**
     * Extracts the CSRF token used by EasyAdmin's shared delete form.
     * Must be called after a page request that renders the delete form (e.g. the index page).
     */
    private function extractDeleteToken(): string
    {
        /** @var KernelBrowser $this->client */
        return (string) $this->client->getCrawler()
            ->filter('form[method="post"] input[name="token"]')
            ->attr('value');
    }
}
