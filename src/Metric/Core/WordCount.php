<?php

namespace HuseyinFiliz\Rewind\Metric\Core;

use Flarum\Post\Post;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;

class WordCount implements RewindMetric
{
    public function key(): string
    {
        return 'word_count';
    }

    public function requiredExtension(): ?string
    {
        return null;
    }

    public function calculate(User $user, int $year): array
    {
        $totalWords = 0;

        Post::where('user_id', $user->id)
            ->where('type', 'comment')
            ->whereYear('created_at', $year)
            ->select('id', 'content')
            ->chunkById(500, function ($posts) use (&$totalWords) {
                foreach ($posts as $post) {
                    $totalWords += \HuseyinFiliz\Rewind\ContentCleaner::countWords($post->content ?? '');
                }
            });

        return ['count' => $totalWords];
    }
}
