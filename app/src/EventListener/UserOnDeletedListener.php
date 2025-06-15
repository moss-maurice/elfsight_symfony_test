<?php

namespace App\EventListener;

use App\Event\UserOnDeletedEvent;
use Psr\Log\LoggerInterface;

readonly class UserOnDeletedListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function __invoke(UserOnDeletedEvent $event): void
    {
        $user = $event->getUser();

        $this->logger->info('User deleted: ' . $user->getEmail());
    }
}
