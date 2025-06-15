<?php

namespace App\Entity;

use App\Entity\Comment;
use App\Repository\EpisodeRepository;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NAME', fields: ['name'])]
class Episode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['default', 'episodes', 'episode'])]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    #[Groups(['default'])]
    private ?string $name = null;

    #[ORM\Column(length: 1024)]
    #[Groups(['default', 'episodes', 'episode'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface  $date = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'episode', orphanRemoval: true)]
    #[Groups(['default', 'comments'])]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?Carbon
    {
        return $this->date ? Carbon::instance($this->date) : null;
    }

    #[Groups(['default', 'episodes', 'episode'])]
    public function getAirDate($format = 'Y-m-d'): string
    {
        return $this->getDate()->format($format);
    }

    public function setDate(Carbon $date): static
    {
        $this->date = $date->toDateTime();

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setEpisode($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getEpisode() === $this) {
                $comment->setEpisode(null);
            }
        }

        return $this;
    }

    #[Groups(['default', 'episodes', 'episode'])]
    public function getAverageSentiment(): float
    {
        $averageSentiment = 0.5;

        if ($this->getComments()->count()) {
            $averageSentiment = array_sum($this->getComments()
                ->map(function ($item) {
                    return $item->getSentiment();
                })
                ->toArray()) / $this->getComments()->count();

            $averageSentiment = round($averageSentiment, 3);
        }


        return $averageSentiment;
    }

    #[Groups(['default', 'episode'])]
    public function getLatestComments(int $limit = 3): array
    {
        $criteria = Criteria::create()
            ->orderBy([
                'date' => Criteria::DESC,
                'id' => Criteria::DESC,
            ])
            ->setMaxResults($limit);

        return array_values($this->comments
            ->matching($criteria)
            ->toArray());
    }
}
