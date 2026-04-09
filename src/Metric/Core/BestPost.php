<?php

namespace HuseyinFiliz\Rewind\Metric\Core;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;
use Illuminate\Database\ConnectionInterface;

class BestPost implements RewindMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function key(): string
    {
        return 'best_post';
    }

    public function requiredExtension(): ?string
    {
        return null;
    }

    public function calculate(User $user, int $year): array
    {
        $hasLikes = $this->db->getSchemaBuilder()->hasTable('post_likes');

        if ($hasLikes) {
            return $this->bestByLikes($user, $year);
        }

        return $this->bestByReplies($user, $year);
    }

    protected function bestByLikes(User $user, int $year): array
    {
        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $discussionsTable = $prefix.'discussions';
        $postLikesTable = $prefix.'post_likes';

        $result = $this->db->table('posts')
            ->leftJoin('post_likes', $postsTable.'.id', '=', $postLikesTable.'.post_id')
            ->join('discussions', $postsTable.'.discussion_id', '=', $discussionsTable.'.id')
            ->where($postsTable.'.user_id', $user->id)
            ->where($postsTable.'.type', 'comment')
            ->whereYear($postsTable.'.created_at', $year)
            ->selectRaw($postsTable.'.id, '.$postsTable.'.discussion_id, '.$postsTable.'.content, '.$discussionsTable.'.title as discussion_title, COUNT('.$postLikesTable.'.user_id) as like_count')
            ->groupBy($postsTable.'.id', $postsTable.'.discussion_id', $postsTable.'.content', $discussionsTable.'.title')
            ->orderByDesc('like_count')
            ->first();

        if (! $result || $result->like_count == 0) {
            return $this->bestByReplies($user, $year);
        }

        return [
            'post_id' => (int) $result->id,
            'discussion_id' => (int) $result->discussion_id,
            'discussion_title' => $result->discussion_title,
            'metric_type' => 'likes',
            'count' => (int) $result->like_count,
            'content_html' => $this->renderContent($result->content ?? ''),
        ];
    }

    protected function bestByReplies(User $user, int $year): array
    {
        // Fallback: find the user's post in the discussion with the most comments
        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $discussionsTable = $prefix.'discussions';

        $result = $this->db->table('posts')
            ->join('discussions', $postsTable.'.discussion_id', '=', $discussionsTable.'.id')
            ->where($postsTable.'.user_id', $user->id)
            ->where($postsTable.'.type', 'comment')
            ->whereYear($postsTable.'.created_at', $year)
            ->selectRaw($postsTable.'.id, '.$postsTable.'.discussion_id, '.$postsTable.'.content, '.$discussionsTable.'.title as discussion_title, '.$discussionsTable.'.comment_count')
            ->orderByDesc($discussionsTable.'.comment_count')
            ->first();

        if (! $result || $result->comment_count <= 1) {
            return [
                'post_id' => null,
                'discussion_id' => null,
                'discussion_title' => null,
                'metric_type' => null,
                'count' => 0,
                'content_html' => null,
            ];
        }

        return [
            'post_id' => (int) $result->id,
            'discussion_id' => (int) $result->discussion_id,
            'discussion_title' => $result->discussion_title,
            'metric_type' => 'discussion_comments',
            'count' => (int) $result->comment_count,
            'content_html' => $this->renderContent($result->content ?? ''),
        ];
    }

    protected function renderContent(string $content): string
    {
        try {
            $formatter = resolve(\Flarum\Formatter\Formatter::class);
            return $formatter->render($content);
        } catch (\Throwable $e) {
            return \HuseyinFiliz\Rewind\ContentCleaner::excerpt($content);
        }
    }
}
