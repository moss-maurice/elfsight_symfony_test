<?php

namespace App\Exception\Comment;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Comment not found');
    }
}