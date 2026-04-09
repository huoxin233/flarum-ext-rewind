<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use DateTimeInterface;
use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class TopContributors implements CommunityMetric
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
        return 'top_contributors';
    }

    public function calculate(int $year): array
    {
        $rows = $this->db->table('posts')
            ->select(['user_id', 'created_at'])
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $createdAt = $row->created_at ?? null;
            if ($createdAt instanceof DateTimeInterface) {
                $postYear = (int) $createdAt->format('Y');
            } else {
                $timestamp = strtotime((string) $createdAt);
                if ($timestamp === false) {
                    continue;
                }
                $postYear = (int) date('Y', $timestamp);
            }

            if ($postYear !== $year) {
                continue;
            }

            $userId = (int) $row->user_id;
            if ($userId <= 0) {
                continue;
            }

            $counts[$userId] = ($counts[$userId] ?? 0) + 1;
        }

        arsort($counts);
        $counts = array_slice($counts, 0, 5, true);

        return [
            'users' => array_map(fn ($userId, $postCount) => [
                'user_id' => (int) $userId,
                'username' => null,
                'post_count' => (int) $postCount,
            ], array_keys($counts), array_values($counts)),
        ];
    }
}
