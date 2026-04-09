<?php

namespace HuseyinFiliz\Rewind\Metric\Optional;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;
use Illuminate\Database\ConnectionInterface;

class LikesGiven implements RewindMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function key(): string
    {
        return 'likes_given';
    }

    public function requiredExtension(): ?string
    {
        return 'flarum-likes';
    }

    public function calculate(User $user, int $year): array
    {
        $count = $this->db->table('post_likes')
            ->where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->count();

        return ['count' => $count];
    }
}
