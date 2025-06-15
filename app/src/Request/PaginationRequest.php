<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationRequest
{
    #[Assert\GreaterThanOrEqual(value: 1, message: 'Page must be 1 or more')]
    private int $page = 1;

    #[Assert\GreaterThanOrEqual(value: 1, message: 'Limit must be 1 or more')]
    private int $limit = 20;

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }
}
