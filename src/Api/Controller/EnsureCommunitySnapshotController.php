<?php

namespace HuseyinFiliz\Rewind\Api\Controller;

use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\PermissionDeniedException;
use HuseyinFiliz\Rewind\CommunitySnapshotGenerator;
use HuseyinFiliz\Rewind\RewindYear;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EnsureCommunitySnapshotController implements RequestHandlerInterface
{
    public function __construct(
        protected CommunitySnapshotGenerator $generator,
        protected RewindYear $years,
        protected SettingsRepositoryInterface $settings,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertCan('huseyinfiliz-rewind.viewForum');

        if (! $actor->is_email_confirmed || ! $this->settings->get('huseyinfiliz-rewind.enabled') || ! $this->settings->get('huseyinfiliz-rewind.automatic_community')) {
            throw new PermissionDeniedException;
        }

        $body = $request->getParsedBody();
        if (empty($body)) {
            $raw = (string) $request->getBody();
            $body = json_decode($raw, true) ?: [];
        }

        $year = (int) ($body['year'] ?? $this->years->active());
        if ($year !== $this->years->active() || $year > $this->years->previous() || $year < 2000) {
            throw new ValidationException(['year' => 'Automatic generation is limited to the active, completed year.']);
        }

        try {
            $progress = $this->generator->step($year);
        } catch (ValidationException $e) {
            if (! isset($e->getAttributes()['rate_limit'])) {
                throw $e;
            }

            $progress = ['ready' => false, 'busy' => true];
        }

        return new JsonResponse($progress, $progress['ready'] ? 200 : 202);
    }
}
