<?php

namespace HuseyinFiliz\Rewind\Metric\Optional;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;
use Illuminate\Database\ConnectionInterface;

class BadgesEarned implements RewindMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function key(): string
    {
        return 'badges_earned';
    }

    public function requiredExtension(): ?string
    {
        return 'fof-badges';
    }

    public function calculate(User $user, int $year): array
    {
        $prefix = $this->db->getTablePrefix();
        $badgeUserTable = $prefix.'fof_badge_user';
        $badgesTable = $prefix.'fof_badges';

        $badges = $this->db->table('fof_badge_user')
            ->join('fof_badges', $badgesTable.'.id', '=', $badgeUserTable.'.badge_id')
            ->where($badgeUserTable.'.user_id', $user->id)
            ->whereYear($badgeUserTable.'.earned_at', $year)
            ->select($badgesTable.'.name', $badgesTable.'.icon')
            ->limit(10)
            ->get();

        return [
            'count' => $badges->count(),
            'badges' => $badges->take(3)->map(fn ($b) => [
                'name' => $b->name,
                'icon' => $b->icon,
            ])->toArray(),
        ];
    }
}
