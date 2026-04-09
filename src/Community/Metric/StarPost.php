<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class StarPost implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function requiredExtension(): ?string
    {
        return 'flarum-likes';
    }

    public function key(): string
    {
        return 'star_post';
    }

    public function calculate(int $year): array
    {
        if (! $this->db->getSchemaBuilder()->hasTable('post_likes')) {
            return $this->empty();
        }

        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $postLikesTable = $prefix.'post_likes';
        $discussionsTable = $prefix.'discussions';
        $usersTable = $prefix.'users';

        $result = $this->db->table('posts')
            ->leftJoin('post_likes', $postsTable.'.id', '=', $postLikesTable.'.post_id')
            ->join('discussions', $postsTable.'.discussion_id', '=', $discussionsTable.'.id')
            ->join('users', $postsTable.'.user_id', '=', $usersTable.'.id')
            ->where($postsTable.'.type', 'comment')
            ->whereYear($postsTable.'.created_at', $year)
            ->selectRaw($postsTable.'.id, '.$postsTable.'.user_id, '.$postsTable.'.content, '.$postsTable.'.discussion_id, '.$discussionsTable.'.title as discussion_title, '.$usersTable.'.username, COUNT('.$postLikesTable.'.user_id) as like_count')
            ->groupBy($postsTable.'.id', $postsTable.'.user_id', $postsTable.'.content', $postsTable.'.discussion_id', $discussionsTable.'.title', $usersTable.'.username')
            ->orderByDesc('like_count')
            ->first();

        if (! $result || $result->like_count == 0) {
            return $this->empty();
        }

        try {
            $formatter = resolve(\Flarum\Formatter\Formatter::class);
            $contentHtml = $formatter->render($result->content ?? '');
        } catch (\Throwable $e) {
            $contentHtml = \HuseyinFiliz\Rewind\ContentCleaner::excerpt($result->content ?? '');
        }

        return [
            'post_id' => (int) $result->id,
            'user_id' => (int) $result->user_id,
            'username' => $result->username,
            'discussion_id' => (int) $result->discussion_id,
            'discussion_title' => $result->discussion_title,
            'like_count' => (int) $result->like_count,
            'content_html' => $contentHtml,
        ];
    }

    private function empty(): array
    {
        return [
            'post_id' => null,
            'user_id' => null,
            'username' => null,
            'discussion_id' => null,
            'discussion_title' => null,
            'like_count' => 0,
            'content_html' => null,
        ];
    }
}
