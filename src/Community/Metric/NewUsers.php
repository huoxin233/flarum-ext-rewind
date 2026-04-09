<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Community\CommunityMetric;

class NewUsers implements CommunityMetric
{
    public function requiredExtension(): ?string
    {
        return null;
    }

    public function key(): string
    {
        return 'new_users';
    }

    public function calculate(int $year): array
    {
        $count = User::whereYear('joined_at', $year)->count();

        return ['count' => $count];
    }
}
