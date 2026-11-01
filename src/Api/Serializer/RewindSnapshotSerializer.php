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
        $canModerate = $actor->can('huseyinfiliz-rewind.moderate');

        $data = null;
        if ($isOwner || $canModerate || $snapshot->is_public) {
            $data = $snapshot->data ?? [];
        }

        $canViewContent = $isOwner || $canModerate || $snapshot->is_public;

        return [
            'year' => (int) $snapshot->year,
            'data' => $data,
            'generatedAt' => $this->formatDate($snapshot->generated_at),
            'isPublic' => (bool) $snapshot->is_public,
            'canEdit' => $isOwner || $canModerate,
            'canModerate' => $canModerate,
            'isEmpty' => $canViewContent ? $this->isSnapshotEmpty($snapshot->data) : false,
        ];
    }

    protected function isSnapshotEmpty($data): bool
    {
        if (empty($data) || ! is_array($data)) {
            return true;
        }

        foreach ($data as $key => $value) {
            if (str_starts_with((string) $key, '_')) {
                continue;
            }
            if (! is_array($value)) {
                continue;
            }

            if (isset($value['count']) && $value['count'] > 0) {
                return false;
            }
            if (isset($value['total']) && $value['total'] > 0) {
                return false;
            }
            if (isset($value['interaction_count']) && $value['interaction_count'] > 0) {
                return false;
            }
            if (isset($value['mention_count']) && $value['mention_count'] > 0) {
                return false;
            }
            if (! empty($value['user_id'])) {
                return false;
            }
            if (! empty($value['post_id'])) {
                return false;
            }
            if (! empty($value['words'])) {
                return false;
            }
            if (! empty($value['emojis'])) {
                return false;
            }
            if (isset($value['peak_hour'])) {
                return false;
            }
            if (isset($value['peak_month']) && $value['peak_month'] > 0) {
                return false;
            }
        }

        return true;
    }

    protected function user($snapshot): ?Relationship
    {
        return $this->hasOne($snapshot, BasicUserSerializer::class);
    }
}
