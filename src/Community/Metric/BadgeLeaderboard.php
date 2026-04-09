<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class BadgeLeaderboard implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function requiredExtension(): ?string
    {
        return 'fof-badges';
    }

    public function key(): string
    {
        return 'badge_leaderboard';
    }

    public function calculate(int $year): array
    {
        if (! $this->db->getSchemaBuilder()->hasTable('fof_badge_user')) {
            return ['users' => []];
        }

        $prefix = $this->db->getTablePrefix();
        $badgesTable = $prefix.'fof_badge_user';
        $usersTable = $prefix.'users';

        $users = $this->db->table('fof_badge_user')
            ->join('users', $usersTable.'.id', '=', $badgesTable.'.user_id')
            ->whereYear($badgesTable.'.earned_at', $year)
            ->selectRaw($badgesTable.'.user_id, '.$usersTable.'.username, COUNT(*) as badge_count')
            ->groupBy($badgesTable.'.user_id', $usersTable.'.username')
            ->orderByDesc('badge_count')
            ->limit(5)
            ->get();

        return [
            'users' => $users->map(fn ($u) => [
                'user_id' => (int) $u->user_id,
                'username' => $u->username,
                'badge_count' => (int) $u->badge_count,
            ])->toArray(),
        ];
    }
}
