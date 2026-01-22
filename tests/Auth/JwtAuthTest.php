<?php

namespace App\Tests\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JwtAuthTest extends WebTestCase
{
    public function testLoginSuccess(): void
    {
        $response = static::createClient()->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'test@test.com',
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
        $response = static::createClient()->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'test@test.com',
                'password' => 'wrongpassword'
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
}
