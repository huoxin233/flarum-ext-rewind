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
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Illuminate\Support\Facades\DB;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class YearStatsController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertAdmin();

        $stats = RewindSnapshot::query()
            ->selectRaw('year, count(*) as count')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        return new JsonResponse([
            'years' => $stats->map(fn ($s) => [
                'year' => (int) $s->year,
                'count' => (int) $s->count,
            ])->values()->all(),
        ]);
    }
}
