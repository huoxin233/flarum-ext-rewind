<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use Flarum\Discussion\Discussion;
use HuseyinFiliz\Rewind\Community\CommunityMetric;

class TotalDiscussions implements CommunityMetric
{
    public function requiredExtension(): ?string
    {
        return null;
    }

    public function key(): string
    {
        return 'total_discussions';
    }

    public function calculate(int $year): array
    {
        $count = Discussion::whereYear('created_at', $year)->count();

        return ['count' => $count];
    }
}
