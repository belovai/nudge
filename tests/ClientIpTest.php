<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\EventSubscriber\ClientIpHeaderSubscriber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ClientIpTest extends ApiTestCase
{
    private const PROXY = '10.42.0.5';

    /** @var array<string, array{0: ?string, 1: ?string}> original $_SERVER / $_ENV values */
    private array $originalEnv = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        // Request::$trustedProxies is static; don't leak it into other tests.
        Request::setTrustedProxies([], -1);

        // Restore, don't unset: .env.test values (e.g. RATE_LIMITING_ENABLED=false) live here too.
        foreach ($this->originalEnv as $name => [$server, $env]) {
            if ($server === null) {
                unset($_SERVER[$name]);
            } else {
                $_SERVER[$name] = $server;
            }
            if ($env === null) {
                unset($_ENV[$name]);
            } else {
                $_ENV[$name] = $env;
            }
        }
        $this->originalEnv = [];
    }

    public static function requests(): iterable
    {
        yield 'header from trusted proxy is used' => ['CF-Connecting-IP', self::PROXY, '203.0.113.7', '203.0.113.7'];
        yield 'IPv6 header value is used' => ['CF-Connecting-IP', self::PROXY, '2001:db8::1', '2001:db8::1'];
        yield 'header from untrusted peer is ignored' => ['CF-Connecting-IP', '198.51.100.9', '203.0.113.7', '198.51.100.9'];
        yield 'invalid header value is ignored' => ['CF-Connecting-IP', self::PROXY, 'not-an-ip', self::PROXY];
        yield 'missing header keeps peer' => ['CF-Connecting-IP', self::PROXY, null, self::PROXY];
        yield 'feature disabled' => [null, self::PROXY, '203.0.113.7', self::PROXY];
    }

    #[Test]
    #[DataProvider('requests')]
    public function resolvesClientIp(?string $headerName, string $peer, ?string $headerValue, string $expectedIp): void
    {
        Request::setTrustedProxies(['10.42.0.0/16'], Request::HEADER_X_FORWARDED_FOR);

        $request = Request::create('/', server: ['REMOTE_ADDR' => $peer]);
        if ($headerValue !== null) {
            $request->headers->set('CF-Connecting-IP', $headerValue);
        }

        (new ClientIpHeaderSubscriber($headerName))->onKernelRequest(
            new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST)
        );

        $this->assertSame($expectedIp, $request->getClientIp());
    }

    #[Test]
    public function rateLimitIsPerRealClientBehindProxy(): void
    {
        $this->setEnv('TRUSTED_PROXIES', '10.42.0.0/16');
        $this->setEnv('CLIENT_IP_HEADER', 'CF-Connecting-IP');
        $this->setEnv('RATE_LIMITING_ENABLED', 'true');

        $client = static::createClient();
        $client->getKernelBrowser()->setServerParameter('REMOTE_ADDR', self::PROXY);
        $url = $client->getContainer()->get('router')->generate('api_app_projects_store');

        // Unique per run: the limiter state lives in the test cache between runs.
        $clientA = sprintf('2001:db8::%x:%x', random_int(1, 0xFFFF), random_int(1, 0xFFFF));
        $clientB = sprintf('2001:db8::%x:%x', random_int(1, 0xFFFF), random_int(1, 0xFFFF));

        $create = fn (string $ip) => $client->request('POST', $url, [
            'headers' => ['CF-Connecting-IP' => $ip],
            'json' => ['name' => 'rate limit test'],
        ]);

        // create_api allows 5 per minute.
        for ($i = 0; $i < 5; ++$i) {
            $create($clientA);
            $this->assertResponseStatusCodeSame(201);
        }

        $create($clientA);
        $this->assertResponseStatusCodeSame(429);

        // Same proxy, different real client: own bucket.
        $create($clientB);
        $this->assertResponseStatusCodeSame(201);
    }

    private function setEnv(string $name, string $value): void
    {
        $this->originalEnv[$name] ??= [$_SERVER[$name] ?? null, $_ENV[$name] ?? null];
        $_SERVER[$name] = $_ENV[$name] = $value;
    }
}
