<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\VersionFactory;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Uid\Uuid;

class VersionTest extends ApiTestCase
{
    #[Test]
    public function checkCurrentVersion(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '2.1.5']);

        // Get current version
        $response = $client->request(
            'GET',
            $urlGenerator->generate('api_app_versions_show', ['uuid' => $version->getProject()->getUuid()])
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['success' => true]);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('version', $data['data']);
        $this->assertEquals('2.1.5', $data['data']['version']);
    }

    #[Test]
    public function bumpPatch(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '1.2.3']);

        // Bump patch version
        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_versions_patch', ['uuid' => $version->getProject()->getUuid()]),
            [
                'json' => [
                    'context' => ['type' => 'patch', 'description' => 'Bug fix'],
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['success' => true]);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('version', $data['data']);
        $this->assertEquals('1.2.4', $data['data']['version']);
        $this->assertArrayHasKey('context', $data['data']);
        $this->assertEquals(['type' => 'patch', 'description' => 'Bug fix'], $data['data']['context']);
    }

    #[Test]
    public function bumpMinor(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '1.2.3']);

        // Bump minor version
        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_versions_minor', ['uuid' => $version->getProject()->getUuid()]),
            [
                'json' => [
                    'context' => ['type' => 'minor', 'description' => 'New feature'],
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['success' => true]);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('version', $data['data']);
        $this->assertEquals('1.3.0', $data['data']['version']);
        $this->assertArrayHasKey('context', $data['data']);
        $this->assertEquals(['type' => 'minor', 'description' => 'New feature'], $data['data']['context']);
    }

    #[Test]
    public function bumpMajor(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '1.2.3']);

        // Bump major version
        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_versions_major', ['uuid' => $version->getProject()->getUuid()]),
            [
                'json' => [
                    'context' => ['type' => 'major', 'description' => 'Breaking changes'],
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['success' => true]);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('version', $data['data']);
        $this->assertEquals('2.0.0', $data['data']['version']);
        $this->assertArrayHasKey('context', $data['data']);
        $this->assertEquals(['type' => 'major', 'description' => 'Breaking changes'], $data['data']['context']);
    }

    #[Test]
    public function checkCurrentVersionForNonExistingProject(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        VersionFactory::createOne(['version' => '1.2.3']);

        $nonExistingUuid = Uuid::v4()->toRfc4122();
        $client->request(
            'GET',
            $urlGenerator->generate('api_app_versions_show', ['uuid' => $nonExistingUuid])
        );

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['success' => false]);
    }

    #[Test]
    public function bumpPatchForNonExistingProject(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        VersionFactory::createOne(['version' => '1.2.3']);

        $nonExistingUuid = Uuid::v4()->toRfc4122();
        $client->request(
            'POST',
            $urlGenerator->generate('api_app_versions_patch', ['uuid' => $nonExistingUuid]),
            [
                'json' => [
                    'context' => ['test' => 'non-existing project'],
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['success' => false]);
    }

    #[Test]
    public function bumpMinorForNonExistingProject(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        VersionFactory::createOne(['version' => '1.2.3']);

        $nonExistingUuid = Uuid::v4()->toRfc4122();
        $client->request(
            'POST',
            $urlGenerator->generate('api_app_versions_minor', ['uuid' => $nonExistingUuid]),
            [
                'json' => [
                    'context' => ['test' => 'non-existing project'],
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['success' => false]);
    }

    #[Test]
    public function bumpMajorForNonExistingProject(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        VersionFactory::createOne(['version' => '1.2.3']);

        $nonExistingUuid = Uuid::v4()->toRfc4122();
        $client->request(
            'POST',
            $urlGenerator->generate('api_app_versions_major', ['uuid' => $nonExistingUuid]),
            [
                'json' => [
                    'context' => ['test' => 'non-existing project'],
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['success' => false]);
    }
}
