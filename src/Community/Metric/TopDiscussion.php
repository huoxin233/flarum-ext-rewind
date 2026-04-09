<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class TopDiscussion implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function requiredExtension(): ?string
    {
        return null;
    }

    public function key(): string
    {
        return 'top_discussion';
    }

    public function calculate(int $year): array
    {
        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $discussionsTable = $prefix.'discussions';
        $usersTable = $prefix.'users';

        $result = $this->db->table('posts')
            ->join('discussions', $discussionsTable.'.id', '=', $postsTable.'.discussion_id')
            ->where($postsTable.'.type', 'comment')
            ->whereYear($postsTable.'.created_at', $year)
            ->selectRaw($discussionsTable.'.id, '.$discussionsTable.'.title, '.$discussionsTable.'.slug, COUNT('.$postsTable.'.id) as post_count')
            ->groupBy($discussionsTable.'.id', $discussionsTable.'.title', $discussionsTable.'.slug')
            ->orderByDesc('post_count')
            ->first();

        if (! $result) {
            return ['id' => null, 'title' => null, 'slug' => null, 'post_count' => 0, 'content_html' => null, 'author_username' => null, 'author_id' => null];
        }

        // Get the first post excerpt and author
        $firstPost = $this->db->table('posts')
            ->join('users', $usersTable.'.id', '=', $postsTable.'.user_id')
            ->where($postsTable.'.discussion_id', $result->id)
            ->where($postsTable.'.type', 'comment')
            ->orderBy($postsTable.'.number')
            ->select($postsTable.'.content', $usersTable.'.username', $usersTable.'.id as user_id')
            ->first();

        $contentHtml = null;
        if ($firstPost && $firstPost->content) {
            try {
                $formatter = resolve(\Flarum\Formatter\Formatter::class);
                $contentHtml = $formatter->render($firstPost->content);
            } catch (\Throwable $e) {
                $contentHtml = \HuseyinFiliz\Rewind\ContentCleaner::excerpt($firstPost->content);
            }
        }

        return [
            'id' => (int) $result->id,
            'title' => $result->title,
            'slug' => $result->slug,
            'post_count' => (int) $result->post_count,
            'content_html' => $contentHtml,
            'author_username' => $firstPost?->username,
            'author_id' => $firstPost ? (int) $firstPost->user_id : null,
        ];
    }
}
