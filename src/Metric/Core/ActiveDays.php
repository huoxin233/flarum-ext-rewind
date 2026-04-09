<?php

namespace HuseyinFiliz\Rewind\Metric\Core;

use Flarum\Post\Post;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;

class ActiveDays implements RewindMetric
{
    public function key(): string
    {
        return 'active_days';
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
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as days')
            ->value('days');

        return ['count' => (int) $count];
    }
}
