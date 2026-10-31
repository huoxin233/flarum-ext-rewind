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

use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteBatchController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertAdmin();

        $body = $request->getParsedBody();
        if (empty($body)) {
            $raw = (string) $request->getBody();
            $body = json_decode($raw, true) ?: [];
        }

        $year = (int) ($body['year'] ?? 0);

        if ($year < 2000 || $year > 2100) {
            throw new ValidationException(['year' => 'Year must be between 2000 and 2100.']);
        }

        $query = RewindSnapshot::where('year', $year);

        $groupId = isset($body['group']) && $body['group'] !== null ? (int) $body['group'] : null;
        if ($groupId) {
            $userIds = User::whereHas('groups', fn ($q) => $q->where('groups.id', $groupId))->select('id');
            $query->whereIn('user_id', $userIds);
        }

        $count = $query->delete();

        return new JsonResponse(['deleted' => $count]);
    }
}
