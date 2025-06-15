<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CommentCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 2048)]
    private string $comment;

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = trim($comment);

        return $this;
    }
}
