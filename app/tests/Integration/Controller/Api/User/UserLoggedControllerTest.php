<?php

namespace App\Tests\Integration\Controller\Api\User;

use App\Tests\Traits\AssertsTrait;
use App\Tests\Traits\Custom\UsersTrait;
use App\Tests\Traits\DatabaseRefreshTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserLoggedControllerTest extends WebTestCase
{
    use DatabaseRefreshTrait;
    use AssertsTrait;
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

    public function testLogged(): void
    {
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

        $crawler = $this->client->request('POST', '/api/auth/logged', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'logged',
            'message',
        ], $responseData);

        $this->assertEquals('User logged', $responseData['message']);
    }

    public function testLoggedWithInvalidToken(): void
    {
        $crawler = $this->client->request('POST', '/api/auth/logged', [], [], [
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

    public function testLoggedWithEmptyToken(): void
    {
        $crawler = $this->client->request('POST', '/api/auth/logged', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer ",
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'logged',
            'message',
        ], $responseData);

        $this->assertEquals('User not logged', $responseData['message']);
    }

    public function testLoggedWithoutToken(): void
    {
        $crawler = $this->client->request('POST', '/api/auth/logged', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'logged',
            'message',
        ], $responseData);

        $this->assertEquals('User not logged', $responseData['message']);
    }
}
