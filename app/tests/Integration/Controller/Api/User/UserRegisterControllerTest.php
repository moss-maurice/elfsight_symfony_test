<?php

namespace App\Tests\Integration\Controller\Api\User;

use App\Tests\Traits\AssertsTrait;
use App\Tests\Traits\Custom\UsersTrait;
use App\Tests\Traits\DatabaseRefreshTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserRegisterControllerTest extends WebTestCase
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

    public function testRegisteration(): void
    {
        $crawler = $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => $this->userData['name'],
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User created successfully', $responseData['message']);
    }

    public function testDoubleRegisteration(): void
    {
        $this->addUsers($this->userData['name'], $this->userData['email'], $this->userData['password']);

        $crawler = $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => $this->userData['name'],
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User already exists', $responseData['message']);
    }

    public function testRegisterationWithoutRequiredFields(): void
    {
        $crawler = $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => $this->userData['name'],
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
                    ]
                ],
            ],
        ], $responseData);

        $this->assertEquals('Validation failed', $responseData['message']);
    }

    public function testRegisterationWithInvalidBody(): void
    {
        $crawler = $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Error while unmarshalling request body', $responseData['message']);
    }
}
