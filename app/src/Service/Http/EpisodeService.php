<?php

namespace App\Service\Http;

use App\Entity\User;
use App\Exception\Comment\NotCreatedException;
use App\Exception\Comment\NotFoundException as CommentNotFoundException;
use App\Exception\Episode\NotFoundException as EpisodeNotFoundException;
use App\Exception\User\NotLoggedException;
use App\Request\CommentCreateRequest;
use App\Request\PaginationRequest;
use App\Service\CommentService;
use App\Service\EpisodeService as MainEpisodeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly final class EpisodeService
{
    public function __construct(
        protected MainEpisodeService $episodeService,
        protected CommentService $commentService,
        protected SerializerInterface $serializer,
        protected EventDispatcherInterface $eventDispatcher
    ) {}

    public function episodeList(PaginationRequest $request): JsonResponse
    {
        $episodes = $this->episodeService->listEpisodes($request->getPage(), $request->getLimit());

        $episodesCount = $this->episodeService->countEpisodes();

        return new JsonResponse([
            'items' => $this->serializer->normalize($episodes, JsonEncoder::FORMAT, [
                'groups' => ['episodes'],
            ]),
            'total' => $episodesCount,
            'page' => $request->getPage(),
            'pages' => ceil($episodesCount / $request->getLimit()),
            'limit' => $request->getLimit(),
        ], JsonResponse::HTTP_OK);
    }

    public function episodeItem(int $id): JsonResponse
    {
        if (!$this->episodeService->hasEpisode($id)) {
            throw new EpisodeNotFoundException;
        }

        $episode = $this->episodeService->getEpisode($id);

        return new JsonResponse([
            'item' => $this->serializer->normalize($episode, JsonEncoder::FORMAT, [
                'groups' => ['episode'],
            ]),
        ], JsonResponse::HTTP_OK);
    }

    public function commentList(int $episodeId): JsonResponse
    {
        if (!$this->episodeService->hasEpisode($episodeId)) {
            throw new EpisodeNotFoundException;
        }

        $episode = $this->episodeService->getEpisode($episodeId);

        $comments = $this->commentService->listComments($episode);

        return new JsonResponse([
            'items' => $this->serializer->normalize($comments, JsonEncoder::FORMAT, [
                'groups' => ['comments'],
            ]),
        ], JsonResponse::HTTP_OK);
    }

    public function commentCreate(CommentCreateRequest $request, ?User $user, int $episodeId): JsonResponse
    {
        if (!$user) {
            throw new NotLoggedException;
        }

        if (!$this->episodeService->hasEpisode($episodeId)) {
            throw new EpisodeNotFoundException;
        }

        $episode = $this->episodeService->getEpisode($episodeId);

        $comment = $this->commentService->createComment($user, $episode, $request->getComment());

        $this->commentService->execute();

        if (!$comment) {
            throw new NotCreatedException;
        }

        return new JsonResponse([
            'item' => $this->serializer->normalize($comment, JsonEncoder::FORMAT, [
                'groups' => ['comment'],
            ]),
        ], JsonResponse::HTTP_CREATED);
    }

    public function commentDelete(?User $user, int $episodeId, int $commentId): JsonResponse
    {
        if (!$user) {
            throw new NotLoggedException;
        }

        if (!$this->episodeService->hasEpisode($episodeId)) {
            throw new EpisodeNotFoundException;
        }

        $episode = $this->episodeService->getEpisode($episodeId);

        $comment = $this->commentService->getCommentPerUserEpisode($user, $episode, $commentId);

        if (!$comment) {
            throw new CommentNotFoundException;
        }

        $this->commentService->deleteComment($comment);

        $this->commentService->execute();

        return new JsonResponse([
            'message' => 'Successfully deleted comment',
        ], JsonResponse::HTTP_OK);
    }
}
