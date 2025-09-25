<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class VersionTest extends ApiTestCase
{
    private function createTestProject(string $name = 'Test Project', string $initialVersion = '1.0.0'): string
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'name' => $name,
                    'initialVersion' => $initialVersion,
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);
        return $data['data']['uuid'];
    }

    #[Test]
    public function checkCurrentVersion(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        // Create a test project with initial version
        $projectUuid = $this->createTestProject('Version Test Project', '2.1.5');

        // Get current version
        $response = $client->request(
            'GET',
            $urlGenerator->generate('api_app_versions_show', ['uuid' => $projectUuid])
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

        // Create a test project with initial version
        $projectUuid = $this->createTestProject('Patch Test Project', '1.2.3');

        // Bump patch version
        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_versions_patch', ['uuid' => $projectUuid]),
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

        // Create a test project with initial version
        $projectUuid = $this->createTestProject('Minor Test Project', '1.2.3');

        // Bump minor version
        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_versions_minor', ['uuid' => $projectUuid]),
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

        // Create a test project with initial version
        $projectUuid = $this->createTestProject('Major Test Project', '1.2.3');

        // Bump major version
        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_versions_major', ['uuid' => $projectUuid]),
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
}
