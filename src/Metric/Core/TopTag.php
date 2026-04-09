<?php

namespace HuseyinFiliz\Rewind\Metric\Core;

use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;
use Illuminate\Database\ConnectionInterface;

class TopTag implements RewindMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function key(): string
    {
        return 'top_tag';
    }

    public function requiredExtension(): ?string
    {
        return 'flarum-tags';
    }

    public function calculate(User $user, int $year): array
    {
        if (! $this->db->getSchemaBuilder()->hasTable('discussion_tag') || ! $this->db->getSchemaBuilder()->hasTable('tags')) {
            return ['tag_name' => null, 'tag_slug' => null, 'tag_color' => null, 'tag_icon' => null, 'count' => 0];
        }

        $prefix = $this->db->getTablePrefix();
        $postsTable = $prefix.'posts';
        $discussionTagTable = $prefix.'discussion_tag';
        $tagsTable = $prefix.'tags';

        $result = $this->db->table('posts')
            ->join('discussion_tag', $postsTable.'.discussion_id', '=', $discussionTagTable.'.discussion_id')
            ->join('tags', $discussionTagTable.'.tag_id', '=', $tagsTable.'.id')
            ->where($postsTable.'.user_id', $user->id)
            ->where($postsTable.'.type', 'comment')
            ->whereYear($postsTable.'.created_at', $year)
            ->selectRaw($tagsTable.'.id, '.$tagsTable.'.name, '.$tagsTable.'.slug, '.$tagsTable.'.color, '.$tagsTable.'.icon, COUNT(*) as post_count')
            ->groupBy($tagsTable.'.id', $tagsTable.'.name', $tagsTable.'.slug', $tagsTable.'.color', $tagsTable.'.icon')
            ->orderByDesc('post_count')
            ->orderByDesc($this->db->raw('MAX('.$postsTable.'.created_at)'))
            ->first();

        if (! $result) {
            return ['tag_name' => null, 'tag_slug' => null, 'tag_color' => null, 'tag_icon' => null, 'count' => 0];
        }

        return [
            'tag_name' => $result->name,
            'tag_slug' => $result->slug,
            'tag_color' => $result->color,
            'tag_icon' => $result->icon,
            'count' => (int) $result->post_count,
        ];
    }
}
