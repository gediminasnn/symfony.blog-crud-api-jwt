<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Post;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class PostTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    private $client;
    private $container;

    protected function setUp(): void
    {
        // Initialize the client and container before each test
        $this->client = self::createClient();
        $this->container = self::getContainer();
    }

    private function createUser(string $username, string $password): User
    {
        // Create a new user and hash the password
        $user = new User();
        $user->setUsername($username);
        $user->setPassword(
            $this->container->get('security.user_password_hasher')->hashPassword($user, $password)
        );

        // Persist the user to the database
        $entityManager = $this->container->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function logIn(string $username, string $password): string
    {
        // Use the class-level client
        $response = $this->client->request('POST', '/api/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);

        // Output the response content for debugging if login fails
        if ($response->getStatusCode() !== 200) {
            echo "Login failed with status code " . $response->getStatusCode() . PHP_EOL;
            echo "Response content: " . $response->getContent(false) . PHP_EOL;
        }

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);

        return $data['token'];
    }

    public function testCreatePostAuthorized(): void
    {
        // Create and log in as a user
        $this->createUser('testuser', 'testpass');
        $token = $this->logIn('testuser', 'testpass');

        // Create a new post with authentication
        $response = $this->client->request('POST', '/api/posts', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
            'auth_bearer' => $token,
            'json' => [
                'title' => 'Test Post',
                'content' => 'This is a test post content.',
            ],
        ]);

        // Expect a 201 Created response
        $this->assertResponseStatusCodeSame(201);

        // Verify the response content
        $data = $response->toArray();
        $this->assertEquals('Test Post', $data['title']);
        $this->assertEquals('This is a test post content.', $data['content']);
    }

    public function testGetPostsCollection(): void
    {
        // Use the class-level client
        $this->client->request('GET', '/api/posts');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Additional assertions can be made here, e.g., checking the structure of the response
    }

    public function testCreatePostUnauthorized(): void
    {
        // Attempt to create a post without authentication
        $this->client->request('POST', '/api/posts', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'title' => 'Unauthorized Post',
                'content' => 'This should not be created.',
            ],
        ]);

        // Expect a 401 Unauthorized response
        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreatePostValidationErrors(): void
    {
        // Create and log in as a user
        $this->createUser('testuser', 'testpass');
        $token = $this->logIn('testuser', 'testpass');

        // Attempt to create a post with missing fields
        $response = $this->client->request('POST', '/api/posts', [
        'headers' => [
            'Content-Type' => 'application/ld+json',
        ],
        'auth_bearer' => $token,
        'json' => [
            // 'title' is missing
            'content' => '',
        ],
        ]);

        // Expect a 422 Unprocessable Entity response due to validation errors
        $this->assertResponseStatusCodeSame(422);

        // Get the response data without throwing exceptions
        $data = $response->toArray(false);

        // Check the validation errors
        $this->assertArrayHasKey('violations', $data);
        $this->assertCount(2, $data['violations']); // Both 'title' and 'content' are invalid

        // Optionally, assert the specific validation messages
        $violations = array_column($data['violations'], 'message');
        $this->assertContains('Title should not be blank.', $violations);
        $this->assertContains('Content should not be blank.', $violations);
    }



    public function testUpdatePost(): void
    {
        // Create and log in as a user
        $this->createUser('testuser', 'testpass');
        $token = $this->logIn('testuser', 'testpass');

        // Create a new post
        $response = $this->client->request('POST', '/api/posts', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'auth_bearer' => $token,
            'json' => [
                'title' => 'Original Title',
                'content' => 'Original content.',
            ],
        ]);

        $data = $response->toArray();
        $postId = $data['id'];

        // Update the post using PATCH
        $this->client->request('PATCH', '/api/posts/' . $postId, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'auth_bearer' => $token,
            'json' => [
                'title' => 'Updated Title',
            ],
        ]);

        // Expect a 200 OK response
        $this->assertResponseIsSuccessful();

        // Verify the updated content
        $updatedData = $this->client->getResponse()->toArray();
        $this->assertEquals('Updated Title', $updatedData['title']);
        $this->assertEquals('Original content.', $updatedData['content']);
    }

    public function testDeletePost(): void
    {
        // Create and log in as a user
        $this->createUser('testuser', 'testpass');
        $token = $this->logIn('testuser', 'testpass');

        // Create a new post
        $response = $this->client->request('POST', '/api/posts', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'auth_bearer' => $token,
            'json' => [
                'title' => 'Post to Delete',
                'content' => 'Content to be deleted.',
            ],
        ]);

        $data = $response->toArray();
        $postId = $data['id'];

        // Delete the post
        $this->client->request('DELETE', '/api/posts/' . $postId, [
            'auth_bearer' => $token,
        ]);

        // Expect a 204 No Content response
        $this->assertResponseStatusCodeSame(204);

        // Verify that the post no longer exists
        $this->client->request('GET', '/api/posts/' . $postId);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetSinglePost(): void
    {
        // Use the class-level container and client
        $entityManager = $this->container->get('doctrine')->getManager();

        $post = new Post();
        $post->setTitle('Single Post');
        $post->setContent('Content of the single post.');

        $entityManager->persist($post);
        $entityManager->flush();

        // Retrieve the post
        $this->client->request('GET', '/api/posts/' . $post->getId());
        $this->assertResponseIsSuccessful();

        // Verify the content
        $data = $this->client->getResponse()->toArray();
        $this->assertEquals('Single Post', $data['title']);
        $this->assertEquals('Content of the single post.', $data['content']);
    }

    public function testUpdatePostUnauthorized(): void
    {
        // Use the class-level container and client
        $entityManager = $this->container->get('doctrine')->getManager();

        $post = new Post();
        $post->setTitle('Post to Update');
        $post->setContent('Original content.');

        $entityManager->persist($post);
        $entityManager->flush();

        // Attempt to update the post without authentication using PATCH
        $this->client->request('PATCH', '/api/posts/' . $post->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'title' => 'Updated Title',
            ],
        ]);

        // Expect a 401 Unauthorized response
        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeletePostUnauthorized(): void
    {
        // Use the class-level container and client
        $entityManager = $this->container->get('doctrine')->getManager();

        $post = new Post();
        $post->setTitle('Post to Delete');
        $post->setContent('Content to be deleted.');

        $entityManager->persist($post);
        $entityManager->flush();

        // Attempt to delete the post without authentication
        $this->client->request('DELETE', '/api/posts/' . $post->getId());

        // Expect a 401 Unauthorized response
        $this->assertResponseStatusCodeSame(401);
    }
}
