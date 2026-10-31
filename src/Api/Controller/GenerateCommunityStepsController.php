<?php

namespace HuseyinFiliz\Rewind\Api\Controller;

use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use HuseyinFiliz\Rewind\Community\CommunityMetricRegistry;
use HuseyinFiliz\Rewind\CommunitySnapshotGenerator;
use HuseyinFiliz\Rewind\RewindYear;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GenerateCommunityStepsController implements RequestHandlerInterface
{
    public function __construct(
        protected CommunityMetricRegistry $metricRegistry,
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

        $this->generator->begin($year);

        return new JsonResponse([
            'steps' => $this->metricRegistry->availableKeys(),
        ]);
    }
}
