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
use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\MetricRegistry;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GenerateForUserController implements RequestHandlerInterface
{
    public function __construct(
        protected MetricRegistry $metricRegistry,
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

        $userId = (int) ($body['user_id'] ?? $body['userId'] ?? 0);
        $activeYear = (int) $this->settings->get('huseyinfiliz-rewind.active_year', date('Y'));
        $year = (int) ($body['year'] ?? $activeYear);

        if (! $userId) {
            throw new ValidationException(['userId' => 'User ID is required.']);
        }

        $targetUser = User::findOrFail($userId);
        $data = $this->metricRegistry->compute($targetUser, $year);

        $snapshot = RewindSnapshot::updateOrCreate(
            ['user_id' => $targetUser->id, 'year' => $year],
            [
                'data' => $data,
                'generated_at' => Carbon::now(),
                'is_public' => false,
            ]
        );

        return new JsonResponse([
            'data' => [
                'type' => 'rw-snaps',
                'id' => (string) $snapshot->id,
                'attributes' => [
                    'year' => (int) $snapshot->year,
                    'data' => $snapshot->data,
                    'generatedAt' => $snapshot->generated_at ? $snapshot->generated_at->toIso8601String() : null,
                    'isPublic' => (bool) $snapshot->is_public,
                ],
            ],
        ]);
    }
}
