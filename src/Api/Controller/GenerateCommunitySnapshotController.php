<?php

namespace HuseyinFiliz\Rewind\Api\Controller;

use Carbon\Carbon;
use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use HuseyinFiliz\Rewind\CommunitySnapshotGenerator;
use HuseyinFiliz\Rewind\Model\CommunitySnapshot;
use HuseyinFiliz\Rewind\RewindYear;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GenerateCommunitySnapshotController implements RequestHandlerInterface
{
    public function __construct(
        protected CommunitySnapshotGenerator $generator,
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

        $year = (int) ($body['year'] ?? $this->years->active());
        if ($year < 2000 || $year > $this->years->active()) {
            throw new ValidationException(['year' => 'Invalid recap year.']);
        }

        // Rate limit: 1 regenerate per minute per year
        $existing = CommunitySnapshot::where('year', $year)->first();
        if ($existing && $existing->generated_at && $existing->generated_at->diffInSeconds(Carbon::now()) < 60) {
            throw new ValidationException([
                'rate_limit' => 'Please wait at least 1 minute before regenerating.',
            ]);
        }

        $snapshot = $this->generator->generate($year, true);

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
        ], 201);
    }
}
