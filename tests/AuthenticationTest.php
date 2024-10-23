<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class AuthenticationTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    public function testUnauthorizedAccess(): void
    {
        $client = self::createClient();

        // Attempt to access a protected endpoint without authentication
        $client->request('POST', '/api/posts');

        // Assert that the response status code is 401 Unauthorized
        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthorizedAccess(): void
    {
        $client = self::createClient();
        $container = self::getContainer();

        // Create a new user and hash the password
        $user = new User();
        $user->setUsername('test');
        $user->setPassword(
            $container->get('security.user_password_hasher')->hashPassword($user, '$3CR3T')
        );

        // Persist the user to the database
        $entityManager = $container->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // Log in to retrieve a JWT token
        $response = $client->request('POST', '/api/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'test',
                'password' => '$3CR3T',
            ],
        ]);

        // Assert that the login was successful and a token was returned
        $this->assertResponseIsSuccessful();
        $json = $response->toArray();
        $this->assertArrayHasKey('token', $json);

        // Use the token to access a protected endpoint
        $client->request('POST', '/api/posts', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'title' => 'Test Post',
                'content' => 'This is a test post.',
            ],
            'auth_bearer' => $json['token'],
        ]);

        // Assert that the response status code is 201 Created
        $this->assertResponseStatusCodeSame(201);
    }
}
