<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class TopDiscussion implements CommunityMetric
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
        return 'top_discussion';
    }

    public function calculate(int $year): array
    {
        $prefix = $this->db->getTablePrefix();

        $result = $this->db->table('posts')
            ->join('discussions', 'discussions.id', '=', 'posts.discussion_id')
            ->where('posts.type', 'comment')
            ->whereYear('posts.created_at', $year)
            ->select('discussions.id', 'discussions.title', 'discussions.slug')
            ->selectRaw('COUNT('.$prefix.'posts.id) as post_count')
            ->groupBy('discussions.id', 'discussions.title', 'discussions.slug')
            ->orderByDesc('post_count')
            ->first();

        if (! $result) {
            return [
                'id' => null,
                'title' => null,
                'slug' => null,
                'post_count' => 0,
                'content_html' => null,
                'author_username' => null,
                'author_id' => null
            ];
        }

        $firstPost = $this->db->table('posts')
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->where('posts.discussion_id', $result->id)
            ->where('posts.type', 'comment')
            ->orderBy('posts.number')
            ->select('posts.content', 'users.username', 'users.id as user_id')
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
