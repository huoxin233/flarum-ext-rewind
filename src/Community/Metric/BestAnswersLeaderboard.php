<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class BestAnswersLeaderboard implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function requiredExtension(): ?string
    {
        return 'fof-best-answer';
    }

    public function key(): string
    {
        return 'best_answers_leaderboard';
    }

    public function calculate(int $year): array
    {
        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $usersTable = $prefix.'users';
        $discussionsTable = $prefix.'discussions';

        $users = $this->db->table('discussions')
            ->join('posts', $postsTable.'.id', '=', $discussionsTable.'.best_answer_post_id')
            ->join('users', $usersTable.'.id', '=', $postsTable.'.user_id')
            ->whereNotNull($discussionsTable.'.best_answer_post_id')
            ->whereYear($discussionsTable.'.best_answer_set_at', $year)
            ->selectRaw($postsTable.'.user_id, '.$usersTable.'.username, COUNT(*) as answer_count')
            ->groupBy($postsTable.'.user_id', $usersTable.'.username')
            ->orderByDesc('answer_count')
            ->limit(5)
            ->get();

        return [
            'users' => $users->map(fn ($u) => [
                'user_id' => (int) $u->user_id,
                'username' => $u->username,
                'answer_count' => (int) $u->answer_count,
            ])->toArray(),
        ];
    }
}
