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
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\PermissionDeniedException;
use HuseyinFiliz\Rewind\Api\Serializer\CommunitySnapshotSerializer;
use HuseyinFiliz\Rewind\Model\CommunitySnapshot;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ShowCommunitySnapshotController extends AbstractShowController
{
    public $serializer = CommunitySnapshotSerializer::class;

    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $id = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);

        $canModerate = $actor->hasPermission('huseyinfiliz-rewind.moderate');
        $enabled = (bool) $this->settings->get('huseyinfiliz-rewind.enabled', false);

        if (! $canModerate && (! $enabled || ! $actor->hasPermission('huseyinfiliz-rewind.viewForum'))) {
            throw new PermissionDeniedException();
        }

        return CommunitySnapshot::findOrFail($id);
    }
}
