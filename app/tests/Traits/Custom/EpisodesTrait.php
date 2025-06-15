<?php

namespace App\Tests\Traits\Custom;

use App\Service\EpisodeService;
use Carbon\Carbon;

trait EpisodesTrait
{
    private function createEpisodes(int $count = 1): void
    {
        $episodeService = static::getContainer()->get(EpisodeService::class);

        for ($i = 0; $i < $count; $i++) {
            $episodeService->createEpisode("s01e{$i}", "Episode {$i}", Carbon::now());
        }

        $episodeService->execute();
    }
}
