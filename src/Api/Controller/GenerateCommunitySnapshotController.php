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
use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use HuseyinFiliz\Rewind\Community\CommunityMetricRegistry;
use HuseyinFiliz\Rewind\Model\CommunitySnapshot;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GenerateCommunitySnapshotController implements RequestHandlerInterface
{
    public function __construct(
        protected CommunityMetricRegistry $metricRegistry,
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertAdmin();

        $year = (int) $this->settings->get('huseyinfiliz-rewind.active_year', date('Y'));

        $existing = CommunitySnapshot::where('year', $year)->first();
        if ($existing && $existing->generated_at && $existing->generated_at->diffInSeconds(Carbon::now()) < 60) {
            throw new ValidationException([
                'rate_limit' => 'Please wait at least 1 minute before regenerating.',
            ]);
        }

        $data = $this->metricRegistry->compute($year);

        $snapshot = CommunitySnapshot::updateOrCreate(
            ['year' => $year],
            ['data' => $data, 'generated_at' => Carbon::now()]
        );

        return new JsonResponse([
            'data' => [
                'type' => 'rw-community',
                'id' => (string) $snapshot->id,
                'attributes' => [
                    'year' => (int) $snapshot->year,
                    'data' => $snapshot->data,
                    'generatedAt' => $snapshot->generated_at ? $snapshot->generated_at->toIso8601String() : null,
                ],
            ],
        ], 201);
    }
}
