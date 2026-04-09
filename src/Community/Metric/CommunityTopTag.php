<?php

namespace HuseyinFiliz\Rewind\Community\Metric;

use HuseyinFiliz\Rewind\Community\CommunityMetric;
use Illuminate\Database\ConnectionInterface;

class CommunityTopTag implements CommunityMetric
{
    public function __construct(
        protected ConnectionInterface $db,
    ) {}

    public function requiredExtension(): ?string
    {
        return 'flarum-tags';
    }

    public function key(): string
    {
        return 'top_tag';
    }

    public function calculate(int $year): array
    {
        if (! $this->db->getSchemaBuilder()->hasTable('tags')) {
            return ['id' => null, 'name' => null, 'slug' => null, 'discussion_count' => 0];
        }

        $prefix = $this->db->getTablePrefix();
        $discussionTagTable = $prefix.'discussion_tag';
        $discussionsTable = $prefix.'discussions';
        $tagsTable = $prefix.'tags';

        $result = $this->db->table('discussion_tag')
            ->join('discussions', $discussionsTable.'.id', '=', $discussionTagTable.'.discussion_id')
            ->join('tags', $tagsTable.'.id', '=', $discussionTagTable.'.tag_id')
            ->whereYear($discussionsTable.'.created_at', $year)
            ->selectRaw($tagsTable.'.id, '.$tagsTable.'.name, '.$tagsTable.'.slug, '.$tagsTable.'.color, COUNT(DISTINCT '.$discussionsTable.'.id) as discussion_count')
            ->groupBy($tagsTable.'.id', $tagsTable.'.name', $tagsTable.'.slug', $tagsTable.'.color')
            ->orderByDesc('discussion_count')
            ->first();

        if (! $result) {
            return ['id' => null, 'name' => null, 'slug' => null, 'discussion_count' => 0];
        }

        return [
            'id' => (int) $result->id,
            'name' => $result->name,
            'slug' => $result->slug,
            'color' => $result->color,
            'discussion_count' => (int) $result->discussion_count,
        ];
    }
}
