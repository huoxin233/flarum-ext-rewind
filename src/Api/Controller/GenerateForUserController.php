<?php

namespace HuseyinFiliz\Rewind\Api\Controller;

use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use HuseyinFiliz\Rewind\RewindYear;
use HuseyinFiliz\Rewind\UserSnapshotGenerator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GenerateForUserController implements RequestHandlerInterface
{
    public function __construct(
        protected UserSnapshotGenerator $generator,
        protected RewindYear $years,
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
        $activeYear = $this->years->active();
        $year = (int) ($body['year'] ?? $activeYear);

        if (! $userId) {
            throw new ValidationException(['userId' => 'A valid user ID is required.']);
        }

        if ($year < 2000 || $year > $activeYear) {
            throw new ValidationException([
                'year' => 'Year must be between 2000 and '.$activeYear,
            ]);
        }

        $targetUser = User::findOrFail($userId);
        $snapshot = $this->generator->generate($targetUser, $year);

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
