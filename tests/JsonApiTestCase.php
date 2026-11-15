<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test base for the JSON API: sends JSON requests through the kernel
 * and checks JSON responses.
 */
abstract class JsonApiTestCase extends WebTestCase
{
    /**
     * Options: `json` is encoded as the body, `body` is sent as is, `headers` adds request headers.
     *
     * @param array{json?: mixed, body?: string, headers?: array<string, string>} $options
     */
    protected function jsonRequest(KernelBrowser $client, string $method, string $uri, array $options = []): Response
    {
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        foreach ($options['headers'] ?? [] as $name => $value) {
            $key = strtoupper(str_replace('-', '_', $name));
            $server[\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true) ? $key : 'HTTP_'.$key] = $value;
        }

        $content = \array_key_exists('json', $options)
            ? json_encode($options['json'], JSON_THROW_ON_ERROR)
            : ($options['body'] ?? null);

        $client->request($method, $uri, server: $server, content: $content);

        return $client->getResponse();
    }

    /**
     * Asserts that the last response is JSON and contains $subset (recursively; extra keys are allowed).
     */
    protected function assertJsonContains(array $subset): void
    {
        $content = static::getClient()->getResponse()->getContent();
        $actual = json_decode($content, true);

        $this->assertIsArray($actual, 'Response is not a JSON object: '.$content);
        $this->assertSubset($subset, $actual);
    }

    private function assertSubset(array $subset, array $actual, string $path = ''): void
    {
        foreach ($subset as $key => $expected) {
            $keyPath = ltrim($path.'.'.$key, '.');
            $this->assertArrayHasKey($key, $actual, sprintf('Missing key "%s" in JSON response.', $keyPath));

            if (\is_array($expected) && \is_array($actual[$key])) {
                $this->assertSubset($expected, $actual[$key], $keyPath);
            } else {
                $this->assertSame($expected, $actual[$key], sprintf('Unexpected value at "%s".', $keyPath));
            }
        }
    }
}
