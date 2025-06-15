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

class EpisodeCommentCreateControllerTest extends WebTestCase
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

    public function testEpisodeCommentCreate(): void
    {
        $this->createEpisodes(1);

        $crawler = $this->client->request('GET', '/api/episode/1/comment');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $responseData['items']);

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

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'item' => [
                'id',
                'user' => [
                    'id',
                    'email',
                    'name',
                ],
                'comment',
                'sentiment',
                'pubDate',
            ],
        ], $responseData);

        $this->assertEquals('Great episode', $responseData['item']['comment']);
        $this->assertEquals($this->userData['email'], $responseData['item']['user']['email']);
        $this->assertEquals($this->userData['name'], $responseData['item']['user']['name']);

        $crawler = $this->client->request('GET', '/api/episode/1/comment');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $responseData['items']);
    }

    public function testEpisodeCommentCreateWithEmptyText(): void
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
            'comment' => '',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'message',
            'details' => [
                'violations' => [
                    [
                        'field',
                        'message',
                    ],
                ],
            ],
        ], $responseData);

        $this->assertEquals('Validation failed', $responseData['message']);
    }

    public function testEpisodeCommentCreateWithoutBody(): void
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
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Error while unmarshalling request body', $responseData['message']);
    }

    public function testEpisodeCommentCreateFromUnauthUser(): void
    {
        $this->createEpisodes(1);

        $crawler = $this->client->request('POST', '/api/episode/1/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer ",
        ], json_encode([
            'comment' => 'Great episode',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Access Denied', $responseData['message']);
    }

    public function testEpisodeCommentCreateFromInvalidUser(): void
    {
        $this->createEpisodes(1);

        $crawler = $this->client->request('POST', '/api/episode/1/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer invalid_token",
        ], json_encode([
            'comment' => 'Great episode',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'code',
            'message',
        ], $responseData);

        $this->assertEquals('Invalid JWT Token', $responseData['message']);
    }

    public function testEpisodeCommentCreateWithoutAuth(): void
    {
        $this->createEpisodes(1);

        $crawler = $this->client->request('GET', '/api/episode/1/comment');

        $crawler = $this->client->request('POST', '/api/episode/1/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'comment' => 'Great episode',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Access Denied', $responseData['message']);
    }

    public function testEpisodeCommentCreateWithGoodSentiment(): void
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

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(0.5, $responseData['item']['sentiment']);
        $this->assertLessThanOrEqual(1, $responseData['item']['sentiment']);
    }

    public function testEpisodeCommentCreateWithBadSentiment(): void
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
            'comment' => 'Bad episode',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(0, $responseData['item']['sentiment']);
        $this->assertLessThanOrEqual(0.5, $responseData['item']['sentiment']);
    }

    public function testEpisodeCommentCreateWithAverageSentiment(): void
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
            'comment' => 'Average episode',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(0.5, $responseData['item']['sentiment']);
    }
}
