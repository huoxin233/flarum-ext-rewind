<?php

namespace HuseyinFiliz\Rewind\Api\Controller;

use Flarum\Http\RequestUtil;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use HuseyinFiliz\Rewind\RewindYear;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MissingUsersController implements RequestHandlerInterface
{
    public function __construct(
        protected RewindYear $years
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

        $year = (int) ($body['year'] ?? $this->years->active());
        $groupId = isset($body['group']) ? (int) $body['group'] : null;

        $query = User::where('is_email_confirmed', true)->orderBy('id');
        $snapshotUsers = RewindSnapshot::where('year', $year)->select('user_id');

        if (! empty($body['regenerate'])) {
            $query->whereIn('id', $snapshotUsers);
        } else {
            $query->whereNotIn('id', $snapshotUsers);
        }

        if ($groupId) {
            $query->whereHas('groups', fn ($q) => $q->where('groups.id', $groupId));
        }

        $users = $query->select(['id', 'username'])->get();

        return new JsonResponse([
            'users' => $users->map(fn ($u) => ['id' => $u->id, 'username' => $u->username])->values()->all(),
        ]);
    }
}
