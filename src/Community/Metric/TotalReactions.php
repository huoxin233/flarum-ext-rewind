<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class TotalReactions implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {
    }

    public function requiredExtension(): ?string
    {
        return 'fof-reactions';
    }

    public function key(): string
    {
        return 'total_reactions';
    }

    public function calculate(int $year): array
    {
        $count = $this->db->table('post_reactions')
            ->join('posts', 'posts.id', '=', 'post_reactions.post_id')
            ->whereYear('post_reactions.created_at', $year)
            ->whereNull('posts.hidden_at')
            ->where('posts.is_private', false)
            ->count();

        return ['count' => $count];
    }
}
