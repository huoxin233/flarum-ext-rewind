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

        $year = isset($body['year']) ? (int) $body['year'] : null;

        if (! $year) {
            throw new ValidationException(['year' => 'Year is required.']);
        }

        $count = RewindSnapshot::where('year', $year)->delete();

        return new JsonResponse(['deleted' => $count]);
    }
}
