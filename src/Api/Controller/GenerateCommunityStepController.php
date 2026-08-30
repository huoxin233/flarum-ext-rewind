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

class GenerateCommunityStepController implements RequestHandlerInterface
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

        $body = $request->getParsedBody();
        if (empty($body)) {
            $raw = (string) $request->getBody();
            $body = json_decode($raw, true) ?: [];
        }

        $step = $body['step'] ?? null;
        $activeYear = (int) $this->settings->get('huseyinfiliz-rewind.active_year', date('Y'));
        $year = (int) ($body['year'] ?? $activeYear);

        if ($year < 2000 || $year > $activeYear) {
            throw new ValidationException([
                'year' => 'Year must be between 2000 and '.$activeYear,
            ]);
        }

        if (! $step || ! is_string($step)) {
            throw new ValidationException([
                'step' => 'A valid metric step key is required.',
            ]);
        }

        $snapshot = CommunitySnapshot::firstOrCreate(
            ['year' => $year],
            ['data' => [], 'generated_at' => Carbon::now()]
        );

        $result = $this->metricRegistry->computeOne($step, $year);

        if ($result !== null) {
            $data = $snapshot->data ?? [];
            $data[$step] = $result;
            $snapshot->data = $data;
            $snapshot->generated_at = Carbon::now();
            $snapshot->save();
        }

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
        ]);
    }
}
