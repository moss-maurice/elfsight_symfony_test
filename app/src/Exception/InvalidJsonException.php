<?php

namespace App\Exception;

use RuntimeException;

class InvalidJsonException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Invalid JSON: ' . $this->getMessage());
    }
}
