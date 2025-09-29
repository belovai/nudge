<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class RateLimiterRegistry
{
    /** @var array<string, RateLimiterFactoryInterface> */
    private array $rateLimiters = [];

    public function add(string $name, RateLimiterFactoryInterface $rateLimiter): void
    {
        $this->rateLimiters[$name] = $rateLimiter;
    }

    public function get(string $name): RateLimiterFactoryInterface
    {
        if (!isset($this->rateLimiters[$name])) {
            throw new \InvalidArgumentException(sprintf('Rate limiter "%s" is not registered.', $name));
        }

        return $this->rateLimiters[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->rateLimiters[$name]);
    }
}
