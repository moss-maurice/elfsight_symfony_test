<?php

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait DatabaseRefreshTrait
{
    protected function refreshDatabase(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--env' => 'test',
            '--no-interaction' => true,
        ]), new BufferedOutput());

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->clear();
    }
}
