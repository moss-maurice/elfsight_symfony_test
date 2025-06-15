<?php

namespace App\DataFixtures;

use App\Service\FakerService;
use App\Service\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserFixtures extends Fixture
{
    private readonly Generator $faker;

    public function __construct(
        private readonly UserService $service,
        private readonly ParameterBagInterface $parameterBag,
        FakerService $faker
    ) {
        $this->faker = $faker->generator();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < $this->parameterBag->get('app.fixturesUsersLimit'); $i++) {
            $this->service->create($this->faker->name(), $this->faker->email(), $this->faker->password(8, 12));
        }

        $this->service->execute();
    }
}
