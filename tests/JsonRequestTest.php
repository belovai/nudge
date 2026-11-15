<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\VersionFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class JsonRequestTest extends ApiTestCase
{
    public static function bodies(): iterable
    {
        // Bump endpoints: body is optional.
        yield 'bump: empty body' => ['bump', '', 201];
        yield 'bump: empty object' => ['bump', '{}', 201];
        yield 'bump: empty array' => ['bump', '[]', 201];
        yield 'bump: JSON null' => ['bump', 'null', 201];
        yield 'bump: invalid JSON' => ['bump', '{"context":', 400];
        yield 'bump: JSON string' => ['bump', '"context"', 400];
        yield 'bump: JSON list' => ['bump', '[1,2]', 400];
        yield 'bump: context wrong type' => ['bump', '{"context":"text"}', 422, 'context'];

        // Project creation: body is required.
        yield 'project: empty body' => ['project', '', 400];
        yield 'project: JSON null' => ['project', 'null', 400];
        yield 'project: invalid JSON' => ['project', '{"name":', 400];
        yield 'project: empty object' => ['project', '{}', 422, 'name'];
        yield 'project: name wrong type' => ['project', '{"name":123}', 422, 'name'];
        yield 'project: initial_version wrong type' => ['project', '{"name":"x","initial_version":1}', 422, 'initial_version'];
        yield 'project: initial_version invalid format' => ['project', '{"name":"x","initial_version":"1.0"}', 422, 'initial_version'];

        // Build creation: body is required.
        yield 'build: empty body' => ['build', '', 400];
        yield 'build: empty object' => ['build', '{}', 422, 'tag'];
        yield 'build: tag wrong type' => ['build', '{"tag":["beta"]}', 422, 'tag'];
    }

    #[Test]
    #[DataProvider('bodies')]
    public function handlesRequestBody(string $endpoint, string $body, int $expectedStatus, ?string $errorField = null): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '1.0.0']);
        $uuid = $version->getProject()->getUuid();

        $url = match ($endpoint) {
            'bump' => $urlGenerator->generate('api_app_versions_patch', ['uuid' => $uuid]),
            'project' => $urlGenerator->generate('api_app_projects_store'),
            'build' => $urlGenerator->generate('api_app_builds_store', ['uuid' => $uuid, 'version' => '1.0.0']),
        };

        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $body,
        ]);

        $this->assertResponseStatusCodeSame($expectedStatus);
        $data = json_decode($response->getContent(false), true);
        $this->assertSame($expectedStatus < 300, $data['success']);

        if ($errorField !== null) {
            $this->assertArrayHasKey($errorField, $data['errors']);
        }
    }
}
