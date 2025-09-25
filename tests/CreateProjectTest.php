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
}
