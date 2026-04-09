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
        $count = $this->db->table('post_likes')
            ->join('posts', 'posts.id', '=', 'post_likes.post_id')
            ->where('posts.user_id', $user->id)
            ->whereYear('post_likes.created_at', $year)
            ->count();

        return ['count' => $count];
    }
}