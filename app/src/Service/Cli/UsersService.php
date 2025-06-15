<?php

namespace App\Service\Cli;

use App\Service\UserService as MainUserService;
use Carbon\Carbon;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly final class UsersService
{
    public function __construct(
        protected MainUserService $service
    ) {}

    public function userRoleGrants(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $input->getArgument('user');
        $role = $input->getArgument('role');

        try {
            $roleName = 'ROLE_USER';

            switch ($role) {
                case 'admin':
                    $roleName = 'ROLE_ADMIN';

                    break;
                default:
                    break;
            }

            if (!$user) {
                throw new Exception("User not typed!");
            }

            $userObject = $this->service->get($user);

            if (!$userObject) {
                throw new Exception("User not found!");
            }

            $this->service->grantRole($userObject, $roleName);
            $this->service->execute();

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $io->text(Carbon::now()->format('Y-m-d H:i:s.u') . " > " . $exception->getMessage());

            return Command::FAILURE;
        }
    }
}
