<?php

namespace App\Tests\Integration\Controller\Api\User;

use App\Tests\Traits\AssertsTrait;
use App\Tests\Traits\Custom\UsersTrait;
use App\Tests\Traits\DatabaseRefreshTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserLoginControllerTest extends WebTestCase
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

    public function testLogin(): void
    {
        $this->addUsers($this->userData['name'], $this->userData['email'], $this->userData['password']);

        $crawler = $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $responseData);
    }

    public function testLoginNotExistsUser(): void
    {
        $crawler = $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'code',
            'message',
        ], $responseData);

        $this->assertEquals('Invalid credentials.', $responseData['message']);
    }

    public function testRegisterationWithInvalidBody(): void
    {
        $crawler = $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(500);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Internal Server Error', $responseData['message']);
    }
}
