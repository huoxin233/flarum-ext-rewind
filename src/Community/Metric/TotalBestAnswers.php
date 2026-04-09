<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class TotalBestAnswers implements CommunityMetric
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
        return 'total_best_answers';
    }

    public function calculate(int $year): array
    {
        $count = $this->db->table('discussions')
            ->whereNotNull('best_answer_post_id')
            ->whereYear('best_answer_set_at', $year)
            ->count();

        return ['count' => $count];
    }
}
