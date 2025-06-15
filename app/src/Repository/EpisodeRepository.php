<?php

namespace App\Repository;

use App\Entity\Episode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Episode>
 */
class EpisodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    public function getList(): array
    {
        return $this->findBy([]);
    }

    public function getListPaginate(int $page = 1, int $limit = 10): array
    {
        $limit = min(max($limit, 1), 100);

        return $this->findBy([], [], $limit, ($page - 1) * $limit);
    }

    public function getCount(): int
    {
        return $this->count();
    }

    public function getItem(int $id): ?Episode
    {
        return $this->find($id);
    }

    public function getItemByName(string $name): ?Episode
    {
        return $this->findOneBy([
            'name' => $name,
        ]);
    }

    public function hasItem(int $id): bool
    {
        return $this->count(['id' => $id]) > 0;
    }

    public function hasItemByName(string $name): bool
    {
        return $this->count(['name' => $name]) > 0;
    }
}
