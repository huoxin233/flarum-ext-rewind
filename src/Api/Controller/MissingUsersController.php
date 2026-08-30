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

use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MissingUsersController implements RequestHandlerInterface
{
    public function __construct(
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

        $year = (int) ($body['year'] ?? $this->settings->get('huseyinfiliz-rewind.active_year', date('Y')));
        $groupId = isset($body['group']) ? (int) $body['group'] : null;

        $existingUserIds = RewindSnapshot::where('year', $year)->pluck('user_id');

        $query = User::where('is_email_confirmed', true)
            ->whereNotIn('id', $existingUserIds)
            ->orderBy('id');

        if ($groupId) {
            $query->whereHas('groups', fn ($q) => $q->where('groups.id', $groupId));
        }

        $users = $query->select(['id', 'username'])->get();

        return new JsonResponse([
            'users' => $users->map(fn ($u) => ['id' => $u->id, 'username' => $u->username])->values()->all(),
        ]);
    }
}
