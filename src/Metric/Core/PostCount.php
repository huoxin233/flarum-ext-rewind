<?php

namespace HuseyinFiliz\Rewind\Metric\Core;

use Flarum\Post\Post;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;

class PostCount implements RewindMetric
{
    public function key(): string
    {
        return 'post_count';
    }

    public function requiredExtension(): ?string
    {
        return null;
    }

    public function calculate(User $user, int $year): array
    {
        $count = Post::where('user_id', $user->id)
            ->where('type', 'comment')
            ->whereYear('created_at', $year)
            ->count();

        return ['count' => $count];
    }
}
