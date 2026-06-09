<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testApiDocUrlIsSuccessful(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request('GET', '/api/doc');

        self::assertResponseIsSuccessful();
    }

    public function testApiAccountUrlIsSecure(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
    }

   public function testUnknownRouteReturns404(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/route-inexistante');

        self::assertResponseStatusCodeSame(404);
    }

    public function testUserRegistrationWorks(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/registration',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Test',
                'lastName' => 'Smoke',
                'guestNumber' => 2,
                'email' => 'test@smoke.fr',
                'password' => 'password'
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
    }

 public function testLoginFailsWithInvalidCredentials(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'inexistant@test.fr',
                'password' => 'fauxpassword'
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(401);
    }
    
}