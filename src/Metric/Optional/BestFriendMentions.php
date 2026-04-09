<?php

namespace HuseyinFiliz\Rewind\Metric\Optional;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;
use Illuminate\Database\ConnectionInterface;

class BestFriendMentions implements RewindMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function key(): string
    {
        return 'best_friend_mentions';
    }

    public function requiredExtension(): ?string
    {
        return 'flarum-mentions';
    }

    public function calculate(User $user, int $year): array
    {
        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $usersTable = $prefix.'users';
        $mentionsTable = $prefix.'post_mentions_user';

        // Who did the user mention most?
        $mentioned = $this->db->table('post_mentions_user')
            ->join('posts', $postsTable.'.id', '=', $mentionsTable.'.post_id')
            ->join('users', $usersTable.'.id', '=', $mentionsTable.'.mentions_user_id')
            ->where($postsTable.'.user_id', $user->id)
            ->where($mentionsTable.'.mentions_user_id', '!=', $user->id)
            ->whereYear($mentionsTable.'.created_at', $year)
            ->selectRaw($mentionsTable.'.mentions_user_id as user_id, '.$usersTable.'.username, COUNT(*) as mention_count')
            ->groupBy($mentionsTable.'.mentions_user_id', $usersTable.'.username')
            ->orderByDesc('mention_count')
            ->first();

        // Who mentioned the user most?
        $mentionedBy = $this->db->table('post_mentions_user')
            ->join('posts', $postsTable.'.id', '=', $mentionsTable.'.post_id')
            ->join('users', $usersTable.'.id', '=', $postsTable.'.user_id')
            ->where($mentionsTable.'.mentions_user_id', $user->id)
            ->where($postsTable.'.user_id', '!=', $user->id)
            ->whereYear($mentionsTable.'.created_at', $year)
            ->selectRaw($postsTable.'.user_id as user_id, '.$usersTable.'.username, COUNT(*) as mention_count')
            ->groupBy($postsTable.'.user_id', $usersTable.'.username')
            ->orderByDesc('mention_count')
            ->first();

        return [
            'user_id' => $mentioned ? (int) $mentioned->user_id : null,
            'username' => $mentioned?->username,
            'display_name' => $mentioned?->username,
            'mention_count' => $mentioned ? (int) $mentioned->mention_count : 0,
            'mentioned_by_user_id' => $mentionedBy ? (int) $mentionedBy->user_id : null,
            'mentioned_by_username' => $mentionedBy?->username,
            'mentioned_by_count' => $mentionedBy ? (int) $mentionedBy->mention_count : 0,
        ];
    }
}
