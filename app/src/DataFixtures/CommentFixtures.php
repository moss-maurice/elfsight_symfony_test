<?php

namespace App\DataFixtures;

use App\DataFixtures\EpisodeFixtures;
use App\DataFixtures\UserFixtures;
use App\Repository\EpisodeRepository;
use App\Repository\UserRepository;
use App\Service\CommentService;
use App\Service\FakerService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    private readonly Generator $faker;

    public function __construct(
        private readonly CommentService $service,
        private readonly ParameterBagInterface $parameterBag,
        private readonly EpisodeRepository $episodeRepository,
        private readonly UserRepository $userRepository,
        FakerService $faker
    ) {
        $this->faker = $faker->generator();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EpisodeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $userList = $this->userRepository->getAllUsers();
        $episodeList = $this->episodeRepository->getList();

        if (count($userList) and count($episodeList)) {
            foreach ($episodeList as $episode) {
                foreach ($userList as $user) {
                    $this->service->createComment($user, $episode, $this->faker->paragraph());
                }
            }

            $this->service->execute();
        }
    }
}
