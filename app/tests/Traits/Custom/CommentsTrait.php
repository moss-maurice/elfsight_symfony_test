<?php

namespace App\Tests\Traits\Custom;

use App\Service\CommentService;
use App\Service\EpisodeService;
use App\Service\UserService;

trait CommentsTrait
{
    private function createComments(int $count = 1, string $comment = null): void
    {
        $commentService = static::getContainer()->get(CommentService::class);
        $episodeService = static::getContainer()->get(EpisodeService::class);
        $userService = static::getContainer()->get(UserService::class);

        if (($episodeService->countEpisodes() > 0) and ($userService->count() > 0)) {
            $episodes = $episodeService->fullListEpisodes();
            $users = $userService->list();

            foreach ($users as $user) {
                foreach ($episodes as $episode) {
                    for ($i = 0; $i < $count; $i++) {
                        $commentService->createComment($user, $episode, !is_null($comment) ? $comment : $this->getRandomCommentText());
                    }
                }
            }

            $commentService->execute();
        }
    }

    private function getRandomCommentText(): string
    {
        $comments = ['Bad episode', 'Great episode', 'Average episode'];

        return $comments[rand(0, count($comments) - 1)];
    }
}
