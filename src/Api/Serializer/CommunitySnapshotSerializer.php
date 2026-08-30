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
use HuseyinFiliz\Rewind\Model\CommunitySnapshot;
use InvalidArgumentException;

class CommunitySnapshotSerializer extends AbstractSerializer
{
    protected $type = 'rw-community';

    /**
     * @param CommunitySnapshot $snapshot
     */
    protected function getDefaultAttributes($snapshot): array
    {
        if (! ($snapshot instanceof CommunitySnapshot)) {
            throw new InvalidArgumentException(
                get_class($this).' can only serialize instances of '.CommunitySnapshot::class
            );
        }

        return [
            'year' => (int) $snapshot->year,
            'data' => $snapshot->data ?? [],
            'generatedAt' => $this->formatDate($snapshot->generated_at),
        ];
    }
}
