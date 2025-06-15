<?php

namespace App\Tests\Integration\Controller\Api\Episode;

use App\Tests\Traits\AssertsTrait;
use App\Tests\Traits\Custom\CommentsTrait;
use App\Tests\Traits\Custom\EpisodesTrait;
use App\Tests\Traits\Custom\UsersTrait;
use App\Tests\Traits\DatabaseRefreshTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EpisodeCommentDeleteControllerTest extends WebTestCase
{
    use DatabaseRefreshTrait;
    use AssertsTrait;
    use CommentsTrait;
    use EpisodesTrait;
    use UsersTrait;

    private readonly KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->refreshDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testEpisodeCommentDelete(): void
    {
        $this->createEpisodes(1);

        $this->addUsers($this->userData['name'], $this->userData['email'], $this->userData['password']);

        $crawler = $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $token = $responseData['token'];

        $crawler = $this->client->request('POST', '/api/episode/1/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
        ], json_encode([
            'comment' => 'Great episode',
        ]));

        $crawler = $this->client->request('DELETE', '/api/episode/1/comment/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Successfully deleted comment', $responseData['message']);
    }

    public function testEpisodeCommentDeleteNotPermittedComment(): void
    {
        $this->createEpisodes(1);
        $this->createUsers(1);
        $this->createComments(10);

        $this->addUsers($this->userData['name'], $this->userData['email'], $this->userData['password']);

        $crawler = $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $token = $responseData['token'];

        $crawler = $this->client->request('DELETE', '/api/episode/1/comment/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Comment not found', $responseData['message']);
    }

    public function testEpisodeCommentDeleteNotExistsComment(): void
    {
        $this->createEpisodes(1);

        $this->addUsers($this->userData['name'], $this->userData['email'], $this->userData['password']);

        $crawler = $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $token = $responseData['token'];

        $crawler = $this->client->request('DELETE', '/api/episode/1/comment/1234567890', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Comment not found', $responseData['message']);
    }

    public function testEpisodeCommentDeleteFromUnauthUser(): void
    {
        $this->createEpisodes(1);

        $this->addUsers($this->userData['name'], $this->userData['email'], $this->userData['password']);

        $crawler = $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $token = $responseData['token'];

        $crawler = $this->client->request('POST', '/api/episode/1/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
        ], json_encode([
            'comment' => 'Great episode',
        ]));

        $crawler = $this->client->request('DELETE', '/api/episode/1/comment/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer ",
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Access Denied', $responseData['message']);
    }

    public function testEpisodeCommentDeleteFromInvalidUser(): void
    {
        $this->createEpisodes(1);

        $this->addUsers($this->userData['name'], $this->userData['email'], $this->userData['password']);

        $crawler = $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $token = $responseData['token'];

        $crawler = $this->client->request('POST', '/api/episode/1/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
        ], json_encode([
            'comment' => 'Great episode',
        ]));

        $crawler = $this->client->request('DELETE', '/api/episode/1/comment/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer invalid_token",
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'code',
            'message',
        ], $responseData);

        $this->assertEquals('Invalid JWT Token', $responseData['message']);
    }

    public function testEpisodeCommentDeleteWithoutAuth(): void
    {
        $this->createEpisodes(1);

        $this->addUsers($this->userData['name'], $this->userData['email'], $this->userData['password']);

        $crawler = $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $token = $responseData['token'];

        $crawler = $this->client->request('POST', '/api/episode/1/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
        ], json_encode([
            'comment' => 'Great episode',
        ]));

        $crawler = $this->client->request('DELETE', '/api/episode/1/comment/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Access Denied', $responseData['message']);
    }
}
