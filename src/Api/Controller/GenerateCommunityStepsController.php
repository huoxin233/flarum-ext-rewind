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
use HuseyinFiliz\Rewind\Community\CommunityMetricRegistry;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GenerateCommunityStepsController implements RequestHandlerInterface
{
    public function __construct(
        protected CommunityMetricRegistry $metricRegistry
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertAdmin();

        return new JsonResponse([
            'steps' => $this->metricRegistry->availableKeys(),
        ]);
    }
}
