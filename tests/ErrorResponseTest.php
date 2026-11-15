<?php

declare(strict_types=1);

namespace App\Tests;

use App\Factory\VersionFactory;
use PHPUnit\Framework\Attributes\Test;

class ErrorResponseTest extends JsonApiTestCase
{
    #[Test]
    public function wrongMethodReturnsJsonWithAllowHeader(): void
    {
        $client = static::createClient();
        $version = VersionFactory::createOne(['version' => '1.0.0']);
        $url = $client->getContainer()->get('router')->generate('api_app_versions_patch', ['uuid' => $version->getProject()->getUuid()]);

        $this->jsonRequest($client, 'GET', $url);

        $this->assertResponseStatusCodeSame(405);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertResponseHeaderSame('Allow', 'POST');
        $this->assertJsonContains(['success' => false]);
    }

    #[Test]
    public function unknownRouteReturnsJson(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'GET', '/no/such/route');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJsonContains(['success' => false]);
    }

    #[Test]
    public function apiPlatformDocsAreGone(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'GET', '/api/docs');

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['success' => false]);
    }
}
