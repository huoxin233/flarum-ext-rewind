<?php

namespace HuseyinFiliz\Rewind\Metric\Optional;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;
use Illuminate\Database\ConnectionInterface;

class BestAnswers implements RewindMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {
    }

    public function key(): string
    {
        return 'best_answers';
    }

    public function requiredExtension(): ?string
    {
        return 'fof-best-answer';
    }

    public function calculate(User $user, int $year): array
    {
        $count = $this->db->table('discussions')
            ->join('posts', 'posts.id', '=', 'discussions.best_answer_post_id')
            ->where('posts.user_id', $user->id)
            ->whereNull('posts.hidden_at')
            ->where('posts.is_private', false)
            ->whereNull('discussions.hidden_at')
            ->where('discussions.is_private', false)
            ->whereYear('discussions.best_answer_set_at', $year)
            ->count();

        return ['count' => $count];
    }
}
