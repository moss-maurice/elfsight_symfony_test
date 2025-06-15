<?php

namespace App\Service;

use Faker\Factory;
use Faker\Generator;

final class FakerService
{
    public function generator(): Generator
    {
        return Factory::create();
    }
}
