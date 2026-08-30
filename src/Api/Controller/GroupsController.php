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

use Flarum\Group\Group;
use Flarum\Http\RequestUtil;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GroupsController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertAdmin();

        $groups = Group::whereNotIn('id', [Group::GUEST_ID])
            ->orderBy('id')
            ->get();

        return new JsonResponse([
            'groups' => $groups->map(fn ($g) => [
                'id' => $g->id,
                'name_singular' => $g->name_singular,
                'name_plural' => $g->name_plural,
                'color' => $g->color,
                'icon' => $g->icon,
            ])->values()->all(),
        ]);
    }
}
