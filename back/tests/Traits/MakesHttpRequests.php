<?php

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** @extends WebTestCase */
trait MakesHttpRequests
{
    use GetsContainerServices;

    /** @var array<string, string> */
    private array $defaultHeaders = ['Content-Type' => 'application/json'];

    private function getCustomClient(): KernelBrowser
    {
        if (!static::$booted) {
            self::createClient();
        }

        /** @var KernelBrowser $client */
        $client = static::getContainer()->get('test.client');
        static::getClient($client);

        return $client;
    }

    /**
     * @param array<string, string|null> $headers Pass null as a value to remove a default header,
     *                                              e.g. ['Content-Type' => null] removes the JSON default.
     * @throws \JsonException
     */
    private function makeRequest(
        string $method,
        string $uri,
        array $headers = [],
        mixed $body = null,
    ): KernelBrowser {
        $merged = array_merge($this->defaultHeaders, $headers);
        $active = array_filter($merged, fn($v) => $v !== null);

        $server = $this->headersToServer($active);
        $encodedBody = $this->encodeBody($body, $active);

        $client = $this->getCustomClient();
        $client->request($method, $uri, [], [], $server, $encodedBody);

        return $client;
    }

    /**
     * @param array<string, string|null> $headers
     */
    private function get(string $uri, array $headers = []): KernelBrowser
    {
        return $this->makeRequest('GET', $uri, $headers);
    }

    /**
     * @param array<string, string|null> $headers
     */
    private function post(string $uri, mixed $body = null, array $headers = []): KernelBrowser
    {
        return $this->makeRequest('POST', $uri, $headers, $body);
    }

    /**
     * @param array<string, string|null> $headers
     */
    private function put(string $uri, mixed $body = null, array $headers = []): KernelBrowser
    {
        return $this->makeRequest('PUT', $uri, $headers, $body);
    }

    /**
     * @param array<string, string|null> $headers
     */
    private function patch(string $uri, mixed $body = null, array $headers = []): KernelBrowser
    {
        return $this->makeRequest('PATCH', $uri, $headers, $body);
    }

    /**
     * @param array<string, string|null> $headers
     */
    private function delete(string $uri, array $headers = []): KernelBrowser
    {
        return $this->makeRequest('DELETE', $uri, $headers);
    }

    /**
     * @param array<string, string|null> $headers
     */
    private function withToken(string $token, array $headers = []): array
    {
        return array_merge(['Authorization' => 'Bearer ' . $token], $headers);
    }

    /**
     * Convert plain header names to Symfony's $_SERVER format.
     *
     * 'Authorization'  → 'HTTP_AUTHORIZATION'
     * 'Content-Type'   → 'CONTENT_TYPE'  (special-cased by Symfony)
     *
     * @param  array<string, string> $headers
     * @return array<string, string>
     */
    private function headersToServer(array $headers): array
    {
        $server = [];

        foreach ($headers as $name => $value) {
            $normalized = strtoupper(str_replace('-', '_', $name));
            $server[$normalized === 'CONTENT_TYPE' ? 'CONTENT_TYPE' : 'HTTP_' . $normalized] = $value;
        }

        return $server;
    }

    /**
     * JSON-encode $body when Content-Type is application/json and body is not already a string.
     *
     * @param array<string, string> $headers  Already-merged, null-stripped headers.
     */
    private function encodeBody(mixed $body, array $headers): ?string
    {
        if ($body === null) {
            return null;
        }

        $contentType = null;
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'content-type') {
                $contentType = $value;
                break;
            }
        }

        if (str_contains((string) $contentType, 'application/json') && !is_string($body)) {
            return json_encode($body, JSON_THROW_ON_ERROR);
        }

        return (string) $body;
    }

    /**
     * @param bool $associative
     * @return ($associative is true ? array<mixed> : object)|string
     * @throws \JsonException
     */
    private function getRequestResponse(bool $associative = true): array|object|string
    {
        $response = static::getClient()->getResponse();
        $content = (string) $response->getContent();
        $contentType = (string) $response->headers->get('Content-Type', '');

        if (str_contains($contentType, 'application/json')) {
            return json_decode($content, $associative, flags: JSON_THROW_ON_ERROR);
        }

        return $content;
    }

    private function getResponseContent(): string
    {
        return (string) static::getClient()->getResponse()->getContent();
    }

    private function getResponseCode(): int
    {
        return static::getClient()->getResponse()->getStatusCode();
    }
}
