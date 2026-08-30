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

use Flarum\Api\Controller\AbstractDeleteController;
use Flarum\Http\RequestUtil;
use Flarum\User\Exception\PermissionDeniedException;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;

class DeleteRewindSnapshotController extends AbstractDeleteController
{
    protected function delete(ServerRequestInterface $request)
    {
        $id = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);

        $snapshot = RewindSnapshot::findOrFail($id);

        $canModerate = $actor->hasPermission('huseyinfiliz-rewind.moderate');

        if (! $canModerate) {
            throw new PermissionDeniedException();
        }

        $snapshot->delete();
    }
}
