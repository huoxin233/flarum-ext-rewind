<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class MostActiveUser implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {
    }

    public function requiredExtension(): ?string
    {
        return null;
    }

    public function key(): string
    {
        return 'most_active_user';
    }

    public function calculate(int $year): array
    {
        $prefix = $this->db->getTablePrefix();

        $result = $this->db->table('posts')
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->where('posts.type', 'comment')
            ->whereYear('posts.created_at', $year)
            ->select('users.id', 'users.username')
            ->selectRaw('COUNT('.$prefix.'posts.id) as post_count')
            ->groupBy('users.id', 'users.username')
            ->orderByDesc('post_count')
            ->first();

        if (! $result) {
            return ['id' => null, 'username' => null, 'post_count' => 0];
        }

        return [
            'id' => (int) $result->id,
            'username' => $result->username,
            'post_count' => (int) $result->post_count,
        ];
    }
}
