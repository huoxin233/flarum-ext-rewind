<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class MostActiveUser implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

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
        $postsTable = $prefix.'posts';
        $usersTable = $prefix.'users';

        $result = $this->db->table('posts')
            ->join('users', $usersTable.'.id', '=', $postsTable.'.user_id')
            ->where($postsTable.'.type', 'comment')
            ->whereYear($postsTable.'.created_at', $year)
            ->selectRaw($usersTable.'.id, '.$usersTable.'.username, COUNT('.$postsTable.'.id) as post_count')
            ->groupBy($usersTable.'.id', $usersTable.'.username')
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
