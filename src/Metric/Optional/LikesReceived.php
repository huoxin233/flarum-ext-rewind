<?php

namespace HuseyinFiliz\Rewind\Metric\Optional;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;
use Illuminate\Database\ConnectionInterface;

class LikesReceived implements RewindMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function key(): string
    {
        return 'likes_received';
    }

    public function requiredExtension(): ?string
    {
        return 'flarum-likes';
    }

    public function calculate(User $user, int $year): array
    {
        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $postLikesTable = $prefix.'post_likes';

        $count = $this->db->table('post_likes')
            ->join('posts', $postsTable.'.id', '=', $postLikesTable.'.post_id')
            ->where($postsTable.'.user_id', $user->id)
            ->whereYear($postLikesTable.'.created_at', $year)
            ->count();

        return ['count' => $count];
    }
}
