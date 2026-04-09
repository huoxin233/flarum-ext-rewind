<?php

namespace HuseyinFiliz\Rewind\Metric\Optional;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;
use Illuminate\Database\ConnectionInterface;

class BestAnswers implements RewindMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function key(): string
    {
        return 'best_answers';
    }

    public function requiredExtension(): ?string
    {
        return 'flarum-best-answer';
    }

    public function calculate(User $user, int $year): array
    {
        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $discussionsTable = $prefix.'discussions';

        $count = $this->db->table('discussions')
            ->join('posts', $postsTable.'.id', '=', $discussionsTable.'.best_answer_post_id')
            ->where($postsTable.'.user_id', $user->id)
            ->whereYear($discussionsTable.'.best_answer_set_at', $year)
            ->count();

        return ['count' => $count];
    }
}
