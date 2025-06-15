<?php

namespace App\Tests\Traits\Custom;

use App\Service\FakerService;
use App\Service\UserService;

trait UsersTrait
{
    private array $userData = [
        'name' => 'Mr. User 5',
        'email' => 'user5@example.com',
        'password' => 'password',
    ];

    private function createUsers(int $count = 1, string $password = null): void
    {
        $userService = static::getContainer()->get(UserService::class);
        $fakerService = static::getContainer()->get(FakerService::class);

        for ($i = 0; $i < $count; $i++) {
            $userService->create($fakerService->generator()->name(), $fakerService->generator()->email(), !is_null($password) ? $password : $fakerService->generator()->password(8, 12));
        }

        $userService->execute();
    }

    private function addUsers(string $name, string $email, string $password): void
    {
        $userService = static::getContainer()->get(UserService::class);

        $userService->create($name, $email, $password);

        $userService->execute();
    }
}
