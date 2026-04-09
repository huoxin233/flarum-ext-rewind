<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class BestAnswersLeaderboard implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {
    }

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
        $users = $this->db->table('discussions')
            ->join('posts', 'posts.id', '=', 'discussions.best_answer_post_id')
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->whereNotNull('discussions.best_answer_post_id')
            ->whereYear('discussions.best_answer_set_at', $year)
            ->select('posts.user_id', 'users.username')
            ->selectRaw('COUNT(*) as answer_count')
            ->groupBy('posts.user_id', 'users.username')
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
