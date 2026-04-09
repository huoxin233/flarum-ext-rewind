<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use Flarum\Post\Post;
use HuseyinFiliz\Rewind\Community\CommunityMetric;

class TotalPosts implements CommunityMetric
{
    public function requiredExtension(): ?string
    {
        return null;
    }

    public function key(): string
    {
        return 'total_posts';
    }

    public function calculate(int $year): array
    {
        $count = Post::where('type', 'comment')
            ->whereYear('created_at', $year)
            ->count();

        return ['count' => $count];
    }
}
