<?php

namespace App\Service;

use App\Entity\Episode;
use App\Repository\EpisodeRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;

final class EpisodeService
{
    private bool $needFlush;

    public function __construct(
        private readonly EpisodeRepository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
        $this->needFlush = false;
    }

    public function createEpisode(string $name, string $title, Carbon $date): Episode
    {
        if ($this->repository->hasItemByName($name)) {
            return $this->repository->getItemByName($name);
        }

        $episodeEntity = new Episode;

        $episodeEntity->setName(mb_strtolower(trim($name)));
        $episodeEntity->setTitle(trim($title));
        $episodeEntity->setDate($date);

        $this->entityManager->persist($episodeEntity);

        $this->needFlush = true;

        return $episodeEntity;
    }

    public function listEpisodes($page = 1, $limit = 10): array
    {
        return $this->repository->getListPaginate($page, $limit);
    }

    public function fullListEpisodes(): array
    {
        return $this->repository->getList();
    }

    public function countEpisodes(): int
    {
        return $this->repository->getCount();
    }

    public function hasEpisode(int $id): bool
    {
        return $this->repository->hasItem($id);
    }

    public function getEpisode(int $id): Episode
    {
        return $this->repository->getItem($id);
    }

    public function execute(): void
    {
        if ($this->needFlush) {
            $this->entityManager->flush();

            $this->needFlush = false;
        }
    }
}
