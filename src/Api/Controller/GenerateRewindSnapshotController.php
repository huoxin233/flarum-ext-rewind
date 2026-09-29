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
use HuseyinFiliz\Rewind\Model\CommunitySnapshot;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class GenerateRewindSnapshotController extends AbstractCreateController
{
    public $serializer = RewindSnapshotSerializer::class;

    public $include = ['user'];

    public function __construct(
        protected MetricRegistry $metricRegistry,
        protected SettingsRepositoryInterface $settings,
        protected ?CacheRepository $cache = null,
    ) {
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $year = (int) $this->settings->get('huseyinfiliz-rewind.active_year', date('Y'));
        $enabled = (bool) $this->settings->get('huseyinfiliz-rewind.enabled', false);

        if (! $actor->can('huseyinfiliz-rewind.moderate')) {
            if (! $enabled) {
                throw new PermissionDeniedException();
            }
            $actor->assertCan('huseyinfiliz-rewind.generate');
        }

        $existing = RewindSnapshot::where('user_id', $actor->id)
            ->where('year', $year)
            ->first();

        if ($existing && ! $actor->can('huseyinfiliz-rewind.moderate')) {
            throw new PermissionDeniedException();
        }

        if ($existing && $existing->generated_at && $existing->generated_at->diffInSeconds(Carbon::now()) < 60) {
            throw new ValidationException([
                'rate_limit' => 'Please wait at least 1 minute before regenerating your rewind.',
            ]);
        }

        if ($this->cache && ! $this->cache->add("rewind_generating_{$actor->id}", 1, 30)) {
            throw new ValidationException([
                'rate_limit' => 'Snapshot generation is already in progress.',
            ]);
        }

        try {
            $data = $this->metricRegistry->compute($actor, $year);

            // Inject community averages if enabled
            if ($this->settings->get('huseyinfiliz-rewind.community_comparison_enabled')) {
                $communitySnapshot = CommunitySnapshot::where('year', $year)->first();
                if ($communitySnapshot && $communitySnapshot->data) {
                    $cd = $communitySnapshot->data;
                    $memberCount = max(1, $cd['new_users']['count'] ?? 1);
                    $totalPosts = $cd['total_posts']['count'] ?? 0;
                    $totalDiscussions = $cd['total_discussions']['count'] ?? 0;
                    $totalWords = $cd['total_words']['total_words'] ?? 0;

                    $data['_community_avg'] = [
                        'posts' => $memberCount > 0 ? round($totalPosts / $memberCount, 1) : 0,
                        'discussions' => $memberCount > 0 ? round($totalDiscussions / $memberCount, 1) : 0,
                        'words' => $memberCount > 0 ? round($totalWords / $memberCount) : 0,
                    ];
                }
            }

            return RewindSnapshot::updateOrCreate(
                ['user_id' => $actor->id, 'year' => $year],
                [
                    'data' => $data,
                    'generated_at' => Carbon::now(),
                    'is_public' => $existing ? $existing->is_public : false,
                ]
            );
        } finally {
            $this->cache?->forget("rewind_generating_{$actor->id}");
        }
    }
}
