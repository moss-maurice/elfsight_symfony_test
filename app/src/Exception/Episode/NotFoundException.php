<?php

namespace App\Exception\Episode;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Episode not found');
    }
}
