<?php

namespace App\Command;

use App\Service\Cli\UsersService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:role',
    description: 'Command to grant user role',
)]
class UserRoleCommand extends Command
{
    public function __construct(
        private readonly UsersService $service
    ) {
        return parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user', InputArgument::REQUIRED, 'User id')
            ->addArgument('role', InputArgument::REQUIRED, 'User role');
    }

    /**
     * cmd: symfony console app:user:create-order-from-basket --user=1 --role=admin
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->service->userRoleGrants($input, $output);
    }
}
