<?php

/*
 * This file is part of huseyinfiliz/rewind.
 *
 * Copyright (c) 2026 Hüseyin Filiz.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace HuseyinFiliz\Rewind\Api\Serializer;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\BasicUserSerializer;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use InvalidArgumentException;
use Tobscure\JsonApi\Relationship;

class RewindSnapshotSerializer extends AbstractSerializer
{
    protected $type = 'rw-snaps';

    /**
     * @param RewindSnapshot $snapshot
     */
    protected function getDefaultAttributes($snapshot): array
    {
        if (! ($snapshot instanceof RewindSnapshot)) {
            throw new InvalidArgumentException(
                get_class($this).' can only serialize instances of '.RewindSnapshot::class
            );
        }

        $actor = $this->getActor();
        $isOwner = (int) $snapshot->user_id === (int) $actor->id;
        $canModerate = $actor->hasPermission('huseyinfiliz-rewind.moderate');

        $data = null;
        if ($isOwner || $canModerate || $snapshot->is_public) {
            $data = $snapshot->data ?? [];
        }

        return [
            'year' => (int) $snapshot->year,
            'data' => $data,
            'generatedAt' => $this->formatDate($snapshot->generated_at),
            'isPublic' => (bool) $snapshot->is_public,
            'canEdit' => $isOwner || $canModerate,
            'canModerate' => $canModerate,
            'isEmpty' => $this->isSnapshotEmpty($snapshot->data),
        ];
    }

    protected function isSnapshotEmpty($data): bool
    {
        if (empty($data) || ! is_array($data)) {
            return true;
        }

        $hasActivity = false;
        foreach ($data as $metric) {
            if (is_array($metric)) {
                if (isset($metric['count']) && $metric['count'] > 0) {
                    $hasActivity = true;
                    break;
                }
                if (isset($metric['interaction_count']) && $metric['interaction_count'] > 0) {
                    $hasActivity = true;
                    break;
                }
                if (isset($metric['mention_count']) && $metric['mention_count'] > 0) {
                    $hasActivity = true;
                    break;
                }
                if (! empty($metric['post_id'])) {
                    $hasActivity = true;
                    break;
                }
                if (! empty($metric['user_id'])) {
                    $hasActivity = true;
                    break;
                }
            }
        }

        return ! $hasActivity;
    }

    protected function user($snapshot): ?Relationship
    {
        return $this->hasOne($snapshot, BasicUserSerializer::class);
    }
}
