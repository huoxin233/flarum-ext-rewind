<?php

namespace HuseyinFiliz\Rewind\Api\Controller;

use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use HuseyinFiliz\Rewind\CommunitySnapshotGenerator;
use HuseyinFiliz\Rewind\RewindYear;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GenerateCommunityStepController implements RequestHandlerInterface
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

        $step = $body['step'] ?? null;
        $activeYear = $this->years->active();
        $year = (int) ($body['year'] ?? $activeYear);

        if ($year < 2000 || $year > $activeYear) {
            throw new ValidationException([
                'year' => 'Year must be between 2000 and '.$activeYear,
            ]);
        }

        if (! $step || ! is_string($step)) {
            throw new ValidationException([
                'step' => 'A valid metric step key is required.',
            ]);
        }

        $progress = $this->generator->step($year, $step, true);

        return new JsonResponse(['data' => $progress]);
    }
}
