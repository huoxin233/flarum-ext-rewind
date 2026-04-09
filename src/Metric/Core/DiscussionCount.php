<?php

namespace HuseyinFiliz\Rewind\Metric\Core;

use Flarum\Discussion\Discussion;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;

class DiscussionCount implements RewindMetric
{
    public function key(): string
    {
        return 'discussion_count';
    }

    public function requiredExtension(): ?string
    {
        return null;
    }

    public function calculate(User $user, int $year): array
    {
        $count = Discussion::where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->count();

        return ['count' => $count];
    }
}
