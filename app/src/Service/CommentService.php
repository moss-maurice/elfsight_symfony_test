<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Service\SentimentAnalyzerService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;

readonly final class CommentService
{
    public function __construct(
        private readonly CommentRepository $repository,
        private readonly SentimentAnalyzerService $sentimentAnalyzerService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function createComment(User $user, Episode $episode, string $comment): Comment
    {
        $commentEntity = new Comment;

        $commentEntity->setUser($user);
        $commentEntity->setEpisode($episode);
        $commentEntity->setComment(trim($comment));
        $commentEntity->setSentiment(strval($this->sentimentAnalyzerService->getSentiment($comment)));
        $commentEntity->setDate(Carbon::now());

        $this->entityManager->persist($commentEntity);

        return $commentEntity;
    }

    public function getCommentPerUserEpisode(User $user, Episode $episode, int $commentId): ?Comment
    {
        return $this->repository->getItemPerUserEpisode($user, $episode, $commentId);
    }

    public function listComments(Episode $episode): array
    {
        return $this->repository->getListPerEpisode($episode);
    }

    public function deleteComment(Comment $comment): bool
    {
        $this->entityManager->remove($comment);

        return !$this->repository->hasItem($comment->getId());
    }

    public function execute(): void
    {
        $this->entityManager->flush();
    }
}
