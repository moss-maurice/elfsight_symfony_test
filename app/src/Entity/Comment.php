<?php

namespace App\Entity;

use App\Entity\Episode;
use App\Entity\User;
use App\Repository\CommentRepository;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['default', 'episodes', 'episode', 'comments', 'comment'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['default'])]
    private ?Episode $episode = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['default', 'episodes', 'episode', 'comments', 'comment'])]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['default', 'episodes', 'episode', 'comments', 'comment'])]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 3, nullable: true)]
    #[Groups(['default', 'episodes', 'episode', 'comments', 'comment'])]
    private ?string $sentiment = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEpisode(): ?Episode
    {
        return $this->episode;
    }

    public function setEpisode(?Episode $episode): static
    {
        $this->episode = $episode;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getSentiment(): ?float
    {
        return $this->sentiment;
    }

    public function setSentiment(?string $sentiment): static
    {
        $this->sentiment = $sentiment;

        return $this;
    }

    public function getDate(): ?Carbon
    {
        return $this->date ? Carbon::instance($this->date) : null;
    }

    #[Groups(['default', 'episodes', 'episode', 'comments', 'comment'])]
    public function getPubDate($format = 'Y-m-d H:i:s'): string
    {
        return $this->getDate()->format($format);
    }

    public function setDate(Carbon $date): static
    {
        $this->date = $date->toDateTime();

        return $this;
    }
}
