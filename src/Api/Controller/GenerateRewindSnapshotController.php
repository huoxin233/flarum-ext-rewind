<?php

namespace HuseyinFiliz\Rewind\Api\Controller;

use Flarum\Api\Controller\AbstractCreateController;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\PermissionDeniedException;
use HuseyinFiliz\Rewind\Api\Serializer\RewindSnapshotSerializer;
use HuseyinFiliz\Rewind\RewindYear;
use HuseyinFiliz\Rewind\UserSnapshotGenerator;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class GenerateRewindSnapshotController extends AbstractCreateController
{
    public $serializer = RewindSnapshotSerializer::class;

    public $include = ['user'];

    public function __construct(
        protected UserSnapshotGenerator $generator,
        protected RewindYear $years,
        protected SettingsRepositoryInterface $settings,
    ) {
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $year = $this->years->active();
        $enabled = (bool) $this->settings->get('huseyinfiliz-rewind.enabled', false);

        if (! $actor->can('huseyinfiliz-rewind.moderate')) {
            if (! $enabled) {
                throw new PermissionDeniedException;
            }
            $actor->assertCan('huseyinfiliz-rewind.generate');
        }

        return $this->generator->generate($actor, $year, $actor->can('huseyinfiliz-rewind.moderate'), true);
    }
}
