<?php

namespace App\EventListener;

use App\Event\UserOnRegisteredEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class UserOnRegisteredListener
{
    public function __construct(
        private LoggerInterface $logger,
        private ParameterBagInterface $parameterBag
    ) {}

    public function __invoke(UserOnRegisteredEvent $event): void
    {
        $user = $event->getUser();

        $this->logger->info('User registered: ' . $user->getEmail());
    }
}
