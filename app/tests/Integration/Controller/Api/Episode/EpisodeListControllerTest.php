<?php

namespace App\Tests\Integration\Controller\Api\Episode;

use App\Tests\Traits\AssertsTrait;
use App\Tests\Traits\Custom\EpisodesTrait;
use App\Tests\Traits\DatabaseRefreshTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EpisodeListControllerTest extends WebTestCase
{
    use DatabaseRefreshTrait;
    use AssertsTrait;
    use EpisodesTrait;

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

    public function testList(): void
    {
        $this->createEpisodes(10);

        $crawler = $this->client->request('GET', '/api/episode', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'page' => 1,
            'limit' => 10,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'items' => [
                [
                    'id',
                    'title',
                    'airDate',
                    'averageSentiment',
                ],
            ],
            'total',
            'page',
            'pages',
            'limit',
        ], $responseData);
    }

    public function testListPagination(): void
    {
        $this->createEpisodes(10);

        $crawler = $this->client->request('GET', '/api/episode', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ], json_encode([
            'page' => 2,
            'limit' => 5
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('total', $responseData);
        $this->assertArrayHasKey('page', $responseData);
        $this->assertArrayHasKey('pages', $responseData);
        $this->assertArrayHasKey('limit', $responseData);

        $this->assertEquals(2, $responseData['page']);
        $this->assertEquals(5, $responseData['limit']);
        $this->assertEquals(10, $responseData['total']);
        $this->assertEquals(2, $responseData['pages']);
    }

    public function testListWithInvalidPagination(): void
    {
        $this->client->request('GET', '/api/episode', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ], json_encode([
            'page' => -1,
            'limit' => 10
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testListWithoutPagination(): void
    {
        $this->createEpisodes(5);

        $this->client->request('GET', '/api/episode', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Error while unmarshalling request body', $responseData['message']);
    }
}
