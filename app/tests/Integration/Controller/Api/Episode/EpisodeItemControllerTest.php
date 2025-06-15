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

class EpisodeItemControllerTest extends WebTestCase
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

    public function testEpisode(): void
    {
        $this->createEpisodes(1);
        $this->createUsers();
        $this->createComments(3);

        $crawler = $this->client->request('GET', '/api/episode/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJsonStructure([
            'item' => [
                'id',
                'title',
                'airDate',
                'averageSentiment',
                'latestComments' => [
                    [
                        'id',
                        'user' => [
                            'id',
                            'email',
                            'name',
                        ],
                        'comment',
                        'sentiment',
                        'pubDate'
                    ]
                ],
            ],
        ], $responseData);
    }

    public function testNotExistsEpisode(): void
    {
        $this->createEpisodes(1);

        $crawler = $this->client->request('GET', '/api/episode/2');

        $this->assertResponseStatusCodeSame(404);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Episode not found', $responseData['message']);
    }

    public function testEpisodeWithoutComments(): void
    {
        $this->createEpisodes(1);

        $this->client->request('GET', '/api/episode/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData['item']['latestComments']);
        $this->assertCount(0, $responseData['item']['latestComments']);
    }

    public function testEpisodeLatestCommentsNoMoreThanThree(): void
    {
        $this->createEpisodes(1);
        $this->createUsers();
        $this->createComments(5);

        $crawler = $this->client->request('GET', '/api/episode/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertLessThanOrEqual(3, count($responseData['item']['latestComments']));
    }

    public function testEpisodeSentiments(): void
    {
        $this->createEpisodes(1);
        $this->createUsers();
        $this->createComments(5);

        $crawler = $this->client->request('GET', '/api/episode/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(0, $responseData['item']['averageSentiment']);
        $this->assertLessThanOrEqual(1, $responseData['item']['averageSentiment']);
    }
}
