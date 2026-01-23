<?php

namespace App\Tests\Auth;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class JwtAuthTest extends ApiTestCase
{
    public function testLoginSuccess(): void
    {
        $response = static::createClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'test@test.com',
                'password' => 'test'
            ]
        ]);

        $this->assertResponseStatusCodeSame(200);

        // Vérifie que la réponse contient un token
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
    }

    public function testLoginFailure(): void
    {
        $response = static::createClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'test@test.com',
                'password' => 'wrongpassword'
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
}
