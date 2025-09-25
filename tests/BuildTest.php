<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\VersionFactory;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Uid\Uuid;

class BuildTest extends ApiTestCase
{
    #[Test]
    public function createBuildForVersion(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '1.0.0']);

        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_builds_store', [
                'uuid' => $version->getProject()->getUuid(),
                'version' => '1.0.0',
            ]),
            [
                'json' => [
                    'tag' => 'release',
                    'context' => ['environment' => 'production', 'commit' => 'abc123'],
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['success' => true]);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('version', $data['data']);
        $this->assertArrayHasKey('context', $data['data']);
        $this->assertEquals('1.0.0-release.1', $data['data']['version']);
        $this->assertEquals(['environment' => 'production', 'commit' => 'abc123'], $data['data']['context']);
    }

    #[Test]
    public function createMultipleBuildsWithSameTag(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '2.1.0']);

        $response1 = $client->request(
            'POST',
            $urlGenerator->generate('api_app_builds_store', [
                'uuid' => $version->getProject()->getUuid(),
                'version' => '2.1.0',
            ]),
            [
                'json' => [
                    'tag' => 'beta',
                    'context' => ['build' => 'first'],
                ],
            ]
        );

        $data1 = json_decode($response1->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('2.1.0-beta.1', $data1['data']['version']);

        // Create second build with same tag - should increment revision
        $response2 = $client->request(
            'POST',
            $urlGenerator->generate('api_app_builds_store', [
                'uuid' => $version->getProject()->getUuid(),
                'version' => '2.1.0',
            ]),
            [
                'json' => [
                    'tag' => 'beta',
                    'context' => ['build' => 'second'],
                ],
            ]
        );

        $data2 = json_decode($response2->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('2.1.0-beta.2', $data2['data']['version']);
    }

    #[Test]
    public function createBuildsWithDifferentTags(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '1.5.3']);

        // Create build with 'alpha' tag
        $response1 = $client->request(
            'POST',
            $urlGenerator->generate('api_app_builds_store', [
                'uuid' => $version->getProject()->getUuid(),
                'version' => '1.5.3',
            ]),
            [
                'json' => [
                    'tag' => 'alpha',
                    'context' => ['type' => 'alpha release'],
                ],
            ]
        );

        $data1 = json_decode($response1->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('1.5.3-alpha.1', $data1['data']['version']);

        // Create build with 'rc' tag - should start from revision 1
        $response2 = $client->request(
            'POST',
            $urlGenerator->generate('api_app_builds_store', [
                'uuid' => $version->getProject()->getUuid(),
                'version' => '1.5.3',
            ]),
            [
                'json' => [
                    'tag' => 'rc',
                    'context' => ['type' => 'release candidate'],
                ],
            ]
        );

        $data2 = json_decode($response2->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('1.5.3-rc.1', $data2['data']['version']);
    }

    #[Test]
    public function createBuildWithoutContext(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '0.9.0']);

        // Create a build without context
        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_builds_store', [
                'uuid' => $version->getProject()->getUuid(),
                'version' => '0.9.0',
            ]),
            [
                'json' => [
                    'tag' => 'snapshot',
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['success' => true]);
        $this->assertEquals('0.9.0-snapshot.1', $data['data']['version']);
        $this->assertNull($data['data']['context']);
    }

    #[Test]
    public function createBuildForNonExistingProject(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        VersionFactory::createOne(['version' => '1.0.0']);

        // Try to create a build for a non-existing project
        $nonExistingUuid = Uuid::v4()->toRfc4122();
        $client->request(
            'POST',
            $urlGenerator->generate('api_app_builds_store', ['uuid' => $nonExistingUuid, 'version' => '1.0.0']),
            [
                'json' => [
                    'tag' => 'release',
                    'context' => ['test' => 'non-existing project'],
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['success' => false]);
    }

    #[Test]
    public function createBuildForNonExistingVersion(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $version = VersionFactory::createOne(['version' => '1.0.0']);

        // Try to create a build for a non-existing version
        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_builds_store', [
                'uuid' => $version->getProject()->getUuid(),
                'version' => '8.0.0',
            ]),
            [
                'json' => [
                    'tag' => 'release',
                    'context' => ['test' => 'non-existing version'],
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['success' => false]);
    }
}
