<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class CreateProjectTest extends ApiTestCase
{
    #[Test]
    public function successfulProjectCreation(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'name' => 'Test Project',
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['success' => true]);
        $this->assertJsonContains(['data' => ['name' => 'Test Project']]);
        $this->assertNotEmpty($data['data']['uuid']);
    }

    #[Test]
    public function successfulProjectCreationWithCustomVersion(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'name' => 'Test Project with Version',
                    'initialVersion' => '1.2.3',
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['success' => true]);
        $this->assertJsonContains(['data' => ['name' => 'Test Project with Version']]);
        $this->assertNotEmpty($data['data']['uuid']);
    }

    #[Test]
    public function projectCreationFailsWithBlankName(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'name' => '',
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function projectCreationFailsWithMissingName(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'initialVersion' => '1.0.0', // Send other field but missing name
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function projectCreationFailsWithNameTooLong(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $longName = str_repeat('a', 256); // 256 characters, exceeds 255 limit

        $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'name' => $longName,
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function projectCreationFailsWithInvalidVersionFormat(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'name' => 'Test Project',
                    'initialVersion' => 'invalid-version',
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function projectCreationFailsWithIncompleteVersionFormat(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'name' => 'Test Project',
                    'initialVersion' => '1.0',
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function projectCreationWithValidLengthName(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $maxLengthName = str_repeat('a', 255); // Exactly 255 characters

        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'name' => $maxLengthName,
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['success' => true]);
        $this->assertJsonContains(['data' => ['name' => $maxLengthName]]);
        $this->assertNotEmpty($data['data']['uuid']);
    }

    #[Test]
    public function projectCreationWithMinimalName(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $response = $client->request(
            'POST',
            $urlGenerator->generate('api_app_projects_store'),
            [
                'json' => [
                    'name' => 'a', // Single character
                ],
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['success' => true]);
        $this->assertJsonContains(['data' => ['name' => 'a']]);
        $this->assertNotEmpty($data['data']['uuid']);
    }
}
