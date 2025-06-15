<?php

namespace App\DataFixtures;

use App\Exception\Episode\ParsingException;
use App\Service\EpisodeService;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EpisodeFixtures extends Fixture
{
    public function __construct(
        private readonly EpisodeService $service,
        private readonly HttpClientInterface $httpClient,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $page = 1;

        do {
            $response = $this->httpClient->request('GET', "https://rickandmortyapi.com/api/episode?page=$page");

            if ($response->getStatusCode() !== 200) {
                throw new ParsingException($response->getStatusCode());
            }

            $data = $response->toArray();

            if (isset($data['results'])) {
                foreach ($data['results'] as $episode) {
                    $this->service->createEpisode(strval($episode['episode']), strval($episode['name']), Carbon::createFromFormat('F j, Y', $episode['air_date']));
                }

                $this->service->execute();
            }

            $page++;
        } while (isset($data['results']) and (count($data['results']) === 20));
    }
}
