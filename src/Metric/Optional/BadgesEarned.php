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
        $badges = $this->db->table('fof_badge_user')
            ->join('fof_badges', 'fof_badges.id', '=', 'fof_badge_user.badge_id')
            ->where('fof_badge_user.user_id', $user->id)
            ->whereYear('fof_badge_user.earned_at', $year)
            ->select('fof_badges.name', 'fof_badges.icon')
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