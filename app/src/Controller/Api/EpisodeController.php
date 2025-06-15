<?php

namespace App\Controller\Api;

use App\Attribute\RequestBody;
use App\Entity\User;
use App\Request\CommentCreateRequest;
use App\Request\PaginationRequest;
use App\Service\Http\EpisodeService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/episode', name: 'api_episode_')]
class EpisodeController extends AbstractController
{
    protected ?User $user;

    public function __construct(
        private readonly EpisodeService $episodeService,
        readonly protected UserService $userService,
    ) {
        $this->user = $this->userService->loggedUser();
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(#[RequestBody] PaginationRequest $request): JsonResponse
    {
        return $this->episodeService->episodeList($request);
    }

    #[Route('/{episodeId}', name: 'episode', requirements: ['episodeId' => '\d+'], methods: ['GET'])]
    public function episode(int $episodeId): JsonResponse
    {
        return $this->episodeService->episodeItem($episodeId);
    }

    #[Route('/{episodeId}/comment', name: 'episode_comment_list', requirements: ['episodeId' => '\d+'], methods: ['GET'])]
    public function episodeCommentList(int $episodeId): JsonResponse
    {
        return $this->episodeService->commentList($episodeId);
    }

    #[Route('/{episodeId}/comment', name: 'episode_comment_create', requirements: ['episodeId' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function episodeCommentCreate(#[RequestBody] CommentCreateRequest $request, int $episodeId): JsonResponse
    {
        return $this->episodeService->commentCreate($request, $this->user, $episodeId);
    }

    #[Route('/{episodeId}/comment/{commentId}', name: 'episode_comment_delete', requirements: ['episodeId' => '\d+', 'commentId' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function episodeCommentDelete(int $episodeId, int $commentId): JsonResponse
    {
        return $this->episodeService->commentDelete($this->user, $episodeId, $commentId);
    }
}
