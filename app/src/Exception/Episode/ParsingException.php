<?php

namespace App\Exception\Episode;

use Throwable;

class ParsingException extends \RuntimeException
{
    public function __construct(int $code, Throwable $previous = null)
    {
        parent::__construct(sprintf('API request failed. Status code: %s', $code), 0, $previous);
    }
}
