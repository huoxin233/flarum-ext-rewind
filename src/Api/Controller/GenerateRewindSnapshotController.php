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

use Carbon\Carbon;
use Flarum\Api\Controller\AbstractCreateController;
use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\PermissionDeniedException;
use HuseyinFiliz\Rewind\Api\Serializer\RewindSnapshotSerializer;
use HuseyinFiliz\Rewind\Metric\MetricRegistry;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class GenerateRewindSnapshotController extends AbstractCreateController
{
    public $serializer = RewindSnapshotSerializer::class;

    public $include = ['user'];

    public function __construct(
        protected MetricRegistry $metricRegistry,
        protected SettingsRepositoryInterface $settings
    ) {
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $enabled = (bool) $this->settings->get('huseyinfiliz-rewind.enabled', false);
        $canGenerate = $actor->hasPermission('huseyinfiliz-rewind.generate');
        $canModerate = $actor->hasPermission('huseyinfiliz-rewind.moderate');

        if (! $canModerate && (! $enabled || ! $canGenerate)) {
            throw new PermissionDeniedException();
        }

        $year = (int) $this->settings->get('huseyinfiliz-rewind.active_year', date('Y'));

        $existing = RewindSnapshot::where('user_id', $actor->id)
            ->where('year', $year)
            ->first();

        if ($existing && ! $canModerate) {
            throw new PermissionDeniedException();
        }

        if ($existing && $existing->generated_at && $existing->generated_at->diffInSeconds(Carbon::now()) < 60) {
            throw new ValidationException([
                'rate_limit' => 'Please wait at least 1 minute before regenerating your rewind.',
            ]);
        }

        $data = $this->metricRegistry->compute($actor, $year);

        return RewindSnapshot::updateOrCreate(
            ['user_id' => $actor->id, 'year' => $year],
            [
                'data' => $data,
                'generated_at' => Carbon::now(),
                'is_public' => $existing ? $existing->is_public : false,
            ]
        );
    }
}
