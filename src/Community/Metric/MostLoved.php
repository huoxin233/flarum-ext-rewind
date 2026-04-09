<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class MostLoved implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function requiredExtension(): ?string
    {
        return 'flarum-likes';
    }

    public function key(): string
    {
        return 'most_loved';
    }

    public function calculate(int $year): array
    {
        if (! $this->db->getSchemaBuilder()->hasTable('post_likes')) {
            return ['users' => []];
        }

        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $postLikesTable = $prefix.'post_likes';
        $usersTable = $prefix.'users';

        $users = $this->db->table('post_likes')
            ->join('posts', $postsTable.'.id', '=', $postLikesTable.'.post_id')
            ->join('users', $usersTable.'.id', '=', $postsTable.'.user_id')
            ->where($postsTable.'.type', 'comment')
            ->whereYear($postLikesTable.'.created_at', $year)
            ->selectRaw($postsTable.'.user_id, '.$usersTable.'.username, COUNT(*) as like_count')
            ->groupBy($postsTable.'.user_id', $usersTable.'.username')
            ->orderByDesc('like_count')
            ->limit(5)
            ->get();

        return [
            'users' => $users->map(fn ($u) => [
                'user_id' => (int) $u->user_id,
                'username' => $u->username,
                'like_count' => (int) $u->like_count,
            ])->toArray(),
        ];
    }
}
