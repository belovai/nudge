<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Attribute\RateLimit;
use App\Service\RateLimiterRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class RateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterRegistry $rateLimiterRegistry,
        private bool $rateLimitingEnabled,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$this->rateLimitingEnabled) {
            return;
        }

        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        $method = new \ReflectionMethod($controller[0], $controller[1]);
        $attributes = $method->getAttributes(RateLimit::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (empty($attributes)) {
            return;
        }

        /** @var RateLimit $rateLimitAttribute */
        $rateLimitAttribute = $attributes[0]->newInstance();
        $limiterName = $rateLimitAttribute->limiterName;

        if (!$this->rateLimiterRegistry->has($limiterName)) {
            throw new \InvalidArgumentException(sprintf('Rate limiter "%s" is not configured.', $limiterName));
        }

        /** @var RateLimiterFactory $limiterFactory */
        $limiterFactory = $this->rateLimiterRegistry->get($limiterName);

        $request = $event->getRequest();
        $limiter = $limiterFactory->create($request->getClientIp());

        $limit = $limiter->consume();
        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
            throw new TooManyRequestsHttpException($retryAfter, 'Too many requests.');
        }
    }
}
