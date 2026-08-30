<?php

/*
 * This file is part of huseyinfiliz/rewind.
 *
 * Copyright (c) 2026 Hüseyin Filiz.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace HuseyinFiliz\Rewind\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Http\RequestUtil;
use Flarum\User\Exception\PermissionDeniedException;
use HuseyinFiliz\Rewind\Api\Serializer\RewindSnapshotSerializer;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class UpdateRewindSnapshotController extends AbstractShowController
{
    public $serializer = RewindSnapshotSerializer::class;

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $id = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);

        $snapshot = RewindSnapshot::findOrFail($id);

        $isOwner = (int) $snapshot->user_id === (int) $actor->id;
        $canModerate = $actor->hasPermission('huseyinfiliz-rewind.moderate');

        if (! $isOwner && ! $canModerate) {
            throw new PermissionDeniedException();
        }

        $body = $request->getParsedBody();
        $attributes = Arr::get($body, 'data.attributes', []);

        if (array_key_exists('isPublic', $attributes)) {
            $snapshot->is_public = (bool) $attributes['isPublic'];
            $snapshot->save();
        }

        return $snapshot;
    }
}
