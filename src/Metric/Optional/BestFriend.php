<?php

namespace HuseyinFiliz\Rewind\Metric\Optional;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;
use Illuminate\Database\ConnectionInterface;

class BestFriend implements RewindMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function key(): string
    {
        return 'best_friend';
    }

    public function requiredExtension(): ?string
    {
        return 'flarum-likes';
    }

    public function calculate(User $user, int $year): array
    {
        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $postLikesTable = $prefix.'post_likes';
        $usersTable = $prefix.'users';
        $mentionsTable = $prefix.'post_mentions_user';

        // Find who liked the user's posts the most
        $result = $this->db->table('post_likes')
            ->join('posts', $postsTable.'.id', '=', $postLikesTable.'.post_id')
            ->join('users', $usersTable.'.id', '=', $postLikesTable.'.user_id')
            ->where($postsTable.'.user_id', $user->id)
            ->where($postLikesTable.'.user_id', '!=', $user->id)
            ->whereYear($postLikesTable.'.created_at', $year)
            ->selectRaw($postLikesTable.'.user_id, '.$usersTable.'.username, COUNT(*) as interaction_count')
            ->groupBy($postLikesTable.'.user_id', $usersTable.'.username')
            ->orderByDesc('interaction_count')
            ->first();

        if (! $result) {
            return [
                'user_id' => null,
                'username' => null,
                'display_name' => null,
                'interaction_count' => 0,
                'likes_received' => 0,
                'likes_given' => 0,
                'mentions_to' => 0,
                'mentions_from' => 0,
            ];
        }

        $friendId = (int) $result->user_id;

        // How many times the user liked the friend's posts
        $likesGiven = (int) $this->db->table('post_likes')
            ->join('posts', $postsTable.'.id', '=', $postLikesTable.'.post_id')
            ->where($postLikesTable.'.user_id', $user->id)
            ->where($postsTable.'.user_id', $friendId)
            ->whereYear($postLikesTable.'.created_at', $year)
            ->count();

        // How many times the user mentioned the friend
        $mentionsTo = 0;
        $mentionsFrom = 0;

        if ($this->db->getSchemaBuilder()->hasTable('post_mentions_user')) {
            $mentionsTo = (int) $this->db->table('post_mentions_user')
                ->join('posts', $postsTable.'.id', '=', $mentionsTable.'.post_id')
                ->where($postsTable.'.user_id', $user->id)
                ->where($mentionsTable.'.mentions_user_id', $friendId)
                ->whereYear($mentionsTable.'.created_at', $year)
                ->count();

            $mentionsFrom = (int) $this->db->table('post_mentions_user')
                ->join('posts', $postsTable.'.id', '=', $mentionsTable.'.post_id')
                ->where($postsTable.'.user_id', $friendId)
                ->where($mentionsTable.'.mentions_user_id', $user->id)
                ->whereYear($mentionsTable.'.created_at', $year)
                ->count();
        }

        return [
            'user_id' => $friendId,
            'username' => $result->username,
            'display_name' => $result->username,
            'interaction_count' => (int) $result->interaction_count,
            'likes_received' => (int) $result->interaction_count,
            'likes_given' => $likesGiven,
            'mentions_to' => $mentionsTo,
            'mentions_from' => $mentionsFrom,
        ];
    }
}
