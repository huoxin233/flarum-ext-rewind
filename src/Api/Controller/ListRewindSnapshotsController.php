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

use Flarum\Api\Controller\AbstractListController;
use Flarum\Http\RequestUtil;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use HuseyinFiliz\Rewind\Api\Serializer\RewindSnapshotSerializer;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ListRewindSnapshotsController extends AbstractListController
{
    public $serializer = RewindSnapshotSerializer::class;

    public $include = ['user'];

    public $sort = ['year' => 'desc'];

    public $sortFields = ['year', 'generated_at'];

    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected UrlGenerator $url
    ) {
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);
        $canModerate = $actor->hasPermission('huseyinfiliz-rewind.moderate');
        $enabled = (bool) $this->settings->get('huseyinfiliz-rewind.enabled', false);

        if (! $canModerate && (! $enabled || ! $actor->hasPermission('huseyinfiliz-rewind.viewForum'))) {
            return [];
        }

        $filters = $this->extractFilter($request);
        $sort = $this->extractSort($request);
        $limit = $this->extractLimit($request);
        $offset = $this->extractOffset($request);

        $query = RewindSnapshot::query();

        if (isset($filters['user'])) {
            $query->where('user_id', (int) $filters['user']);
        }

        if (isset($filters['year'])) {
            $query->where('year', (int) $filters['year']);
        }

        if (! $canModerate) {
            $query->where(function ($q) use ($actor) {
                $q->where('is_public', true);
                if ($actor->id) {
                    $q->orWhere('user_id', $actor->id);
                }
            });
        }

        foreach ($sort ?: [] as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query->skip($offset)->take($limit)->get();
    }
}
