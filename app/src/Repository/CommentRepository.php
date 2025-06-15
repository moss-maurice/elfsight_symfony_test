<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
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

    public function getListPerEpisode(Episode $episode): array
    {
        return $this->findBy(
            ['episode' => $episode],
            [
                'date' => 'DESC',
                'id' => 'DESC',
            ],
        );
    }

    public function getCount(): int
    {
        return $this->count();
    }

    public function getItem(int $id): ?Comment
    {
        return $this->find($id);
    }

    public function getItemPerUserEpisode(User $user, Episode $episode, int $id): ?Comment
    {
        return $this->findOneBy([
            'user' => $user,
            'episode' => $episode,
            'id' => $id,
        ]);
    }

    public function hasItem(int $id): bool
    {
        return $this->count(['id' => $id]) > 0;
    }
}
