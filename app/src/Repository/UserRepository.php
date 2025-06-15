<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    protected $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getAllUsers(): array
    {
        return $this->findBy([]);
    }

    public function getCount(): int
    {
        return $this->count();
    }

    public function hasUser(int $id): bool
    {
        return $this->getUser($id) !== null;
    }

    public function hasUserBy(array $conditions): bool
    {
        return $this->getUserBy($conditions) !== null;
    }

    public function getUser(int $id): ?User
    {
        return $this->find($id);
    }

    public function getUserBy(array $conditions): ?User
    {
        return $this->findOneBy($conditions);
    }
}
