<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use Flarum\Post\Post;
use HuseyinFiliz\Rewind\Community\CommunityMetric;

class TotalWords implements CommunityMetric
{
    public function requiredExtension(): ?string
    {
        return null;
    }

    public function key(): string
    {
        return 'total_words';
    }

    public function calculate(int $year): array
    {
        $totalWords = 0;
        $totalPosts = 0;

        Post::where('type', 'comment')
            ->whereYear('created_at', $year)
            ->select('id', 'content')
            ->chunkById(1000, function ($posts) use (&$totalWords, &$totalPosts) {
                foreach ($posts as $post) {
                    $totalWords += \HuseyinFiliz\Rewind\ContentCleaner::countWords($post->content ?? '');
                    $totalPosts++;
                }
            });

        return [
            'total_words' => $totalWords,
            'total_posts' => $totalPosts,
            'avg_words_per_post' => $totalPosts > 0 ? round($totalWords / $totalPosts, 1) : 0,
        ];
    }
}
