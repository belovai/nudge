<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Resolves the client IP from a single-value header set by the edge proxy
 * (e.g. Cloudflare's CF-Connecting-IP), which Symfony's trusted headers can't express.
 *
 * Needed when a proxy in between rewrites X-Forwarded-For (Traefik does so for
 * peers outside its forwardedHeaders.trustedIPs), so the real client IP only
 * survives in the edge header.
 *
 * The header is honoured only if the request comes from a trusted proxy
 * (TRUSTED_PROXIES); otherwise any client could pick its own IP and dodge
 * rate limiting. The resolved IP replaces REMOTE_ADDR, the same thing nginx's
 * realip module does; since it is not a trusted proxy itself, Request::getClientIp()
 * then returns it and ignores X-Forwarded-For.
 */
final readonly class ClientIpHeaderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(param: 'client_ip_header')]
        private ?string $clientIpHeader,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Before anything that reads the client IP (rate limiter, security, logging).
        return [KernelEvents::REQUEST => ['onKernelRequest', 4096]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->clientIpHeader || !$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isFromTrustedProxy()) {
            return;
        }

        $clientIp = trim((string) $request->headers->get($this->clientIpHeader));
        if (filter_var($clientIp, FILTER_VALIDATE_IP) === false) {
            return;
        }

        $request->server->set('REMOTE_ADDR', $clientIp);
    }
}
