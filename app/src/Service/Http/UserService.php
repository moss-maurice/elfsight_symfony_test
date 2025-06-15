<?php

namespace App\Service\Http;

use App\Exception\User\AlreadyLoggedException;
use App\Request\UserRegisterRequest;
use App\Service\UserService as MainUserService;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly final class UserService
{
    public function __construct(
        protected MainUserService $service,
        protected EventDispatcherInterface $eventDispatcher
    ) {}

    public function createUser(UserRegisterRequest $request): JsonResponse
    {
        $this->service->create($request->getName(), $request->getEmail(), $request->getPassword());
        $this->service->execute();

        return new JsonResponse([
            'message' => 'User created successfully',
        ], JsonResponse::HTTP_CREATED);
    }

    public function registerUser(UserRegisterRequest $request): JsonResponse
    {
        if ($this->service->isLoggedUser()) {
            throw new AlreadyLoggedException;
        }

        $this->service->register($request->getName(), $request->getEmail(), $request->getPassword());
        $this->service->execute();

        return new JsonResponse([
            'message' => 'User created successfully',
        ], JsonResponse::HTTP_CREATED);
    }

    public function isLoggedUser(): JsonResponse
    {
        try {
            $logged = $this->service->isLoggedUser();
        } catch (ExpiredTokenException $exception) {
            return new JsonResponse([
                'logged' => false,
                'message' => $exception->getMessage(),
            ], $exception->getCode());
        }

        return new JsonResponse([
            'logged' => $logged,
            'message' => $logged ? 'User logged' : 'User not logged',
        ], JsonResponse::HTTP_CREATED);
    }
}
