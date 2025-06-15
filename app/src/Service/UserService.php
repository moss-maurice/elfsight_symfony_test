<?php

namespace App\Service;

use App\Entity\User;
use App\Event\UserOnDeletedEvent;
use App\Event\UserOnRegisteredEvent;
use App\Exception\User\AlreadyExistsException;
use App\Exception\User\NotCreatedException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly final class UserService
{
    public function __construct(
        protected UserRepository $repository,
        protected TokenStorageInterface $tokenStorage,
        protected UserPasswordHasherInterface $passwordHasher,
        protected EntityManagerInterface $entityManager,
        protected EventDispatcherInterface $eventDispatcher
    ) {}

    public function create(string $name, string $email, string $password): User
    {
        if ($this->hasByEmail($email)) {
            throw new AlreadyExistsException;
        }

        $user = $this->createUser($name, $email, $password);

        if (!$user) {
            throw new NotCreatedException;
        }

        return $user;
    }

    public function register(string $name, string $email, string $password): User
    {
        $user = $this->create($name, $email, $password);

        $this->eventDispatcher->dispatch(new UserOnRegisteredEvent($user), UserOnRegisteredEvent::NAME);

        return $user;
    }

    public function list(): array
    {
        return $this->repository->getAllUsers();
    }

    public function has(int $id): bool
    {
        return $this->repository->hasUser($id);
    }

    public function count(): int
    {
        return $this->repository->getCount();
    }

    public function hasByName(string $name): bool
    {
        return $this->repository->hasUserBy([
            'name' => $name,
        ]);
    }

    public function hasByEmail(string $email): bool
    {
        return $this->repository->hasUserBy([
            'email' => $email,
        ]);
    }

    public function get(int $id): User
    {
        return $this->repository->getUser($id);
    }

    public function getByName(string $name): User
    {
        return $this->repository->getUserBy([
            'name' => $name,
        ]);
    }

    public function getByEmail(string $email): User
    {
        return $this->repository->getUserBy([
            'email' => $email,
        ]);
    }

    public function delete(int $id): bool
    {
        $user = $this->get($id);

        if ($user) {
            $this->deleteUser($user);

            $this->eventDispatcher->dispatch(new UserOnDeletedEvent($user), UserOnDeletedEvent::NAME);

            return !$this->has($id);
        }

        return false;
    }

    public function deleteByName(string $name): bool
    {
        $user = $this->getByName($name);

        if ($user) {
            $this->deleteUser($user);

            return !$this->hasByName($name);
        }

        return false;
    }

    public function deleteByEmail(string $email): bool
    {
        $user = $this->getByEmail($email);

        if ($user) {
            $this->deleteUser($user);

            return !$this->hasByEmail($email);
        }

        return false;
    }

    public function isLoggedUser(): bool
    {
        return $this->loggedUser() !== null;
    }

    public function loggedUser(): ?User
    {
        return $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
    }

    public function grantRole(User $user, string $role): User
    {
        return $this->grantUser($user, $role);
    }

    protected function upgradePassword(PasswordAuthenticatedUserInterface $user, string $password): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
    }

    protected function createUser(string $name, string $email, string $password): User
    {
        $user = new User();

        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);

        return $user;
    }

    protected function deleteUser(User $user): bool
    {
        $this->entityManager->remove($user);

        return !$this->repository->hasUser($user->getId());
    }

    protected function grantUser(User $user, string $role): User
    {
        $user->setRoles([$role]);

        $this->entityManager->persist($user);

        return $this->repository->getUser($user->getId());
    }

    public function execute(): void
    {
        $this->entityManager->flush();
    }
}
