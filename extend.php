<?php

/*
 * This file is part of huseyinfiliz/rewind.
 *
 * Copyright (c) 2026 Hüseyin Filiz.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace HuseyinFiliz\Rewind;

use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Extend;
use HuseyinFiliz\Rewind\Api\Controller;
use HuseyinFiliz\Rewind\Http\Controller\ShowCommunityRewindBladeController;
use HuseyinFiliz\Rewind\Http\Controller\ShowUserRewindBladeController;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less')
        ->route('/rewind', 'huseyinfiliz-rewind.forum')
        ->route('/u/{username}/rewind', 'huseyinfiliz-rewind.profile'),

    (new Extend\Routes('forum'))
        ->get('/rewind/view/{id:[0-9]+}/{year:[0-9]+}', 'huseyinfiliz-rewind.blade.user', ShowUserRewindBladeController::class)
        ->get('/rewind/view/{year:[0-9]+}', 'huseyinfiliz-rewind.blade.community', ShowCommunityRewindBladeController::class)
        ->get('/rewind/view', 'huseyinfiliz-rewind.blade.community-default', ShowCommunityRewindBladeController::class),

    (new Extend\View())
        ->namespace('rewind', __DIR__.'/resources/views'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ServiceProvider())
        ->register(RewindServiceProvider::class),

    (new Extend\Routes('api'))
        ->get('/rw-snaps', 'huseyinfiliz-rewind.snaps.index', Controller\ListRewindSnapshotsController::class)
        ->get('/rw-snaps/{id}', 'huseyinfiliz-rewind.snaps.show', Controller\ShowRewindSnapshotController::class)
        ->patch('/rw-snaps/{id}', 'huseyinfiliz-rewind.snaps.update', Controller\UpdateRewindSnapshotController::class)
        ->delete('/rw-snaps/{id}', 'huseyinfiliz-rewind.snaps.delete', Controller\DeleteRewindSnapshotController::class)
        ->post('/rw-snaps/generate', 'huseyinfiliz-rewind.snaps.generate', Controller\GenerateRewindSnapshotController::class)
        ->post('/rw-snaps/missing-users', 'huseyinfiliz-rewind.snaps.missing-users', Controller\MissingUsersController::class)
        ->post('/rw-snaps/generate-for-user', 'huseyinfiliz-rewind.snaps.generate-for-user', Controller\GenerateForUserController::class)
        ->post('/rw-snaps/groups', 'huseyinfiliz-rewind.snaps.groups', Controller\GroupsController::class)
        ->post('/rw-snaps/year-stats', 'huseyinfiliz-rewind.snaps.year-stats', Controller\YearStatsController::class)
        ->post('/rw-snaps/batch-delete', 'huseyinfiliz-rewind.snaps.batch-delete', Controller\DeleteBatchController::class)
        ->post('/rw-snaps/delete-batch', 'huseyinfiliz-rewind.snaps.delete-batch', Controller\DeleteBatchController::class)
        ->get('/rw-community', 'huseyinfiliz-rewind.community.index', Controller\ListCommunitySnapshotsController::class)
        ->get('/rw-community/{id}', 'huseyinfiliz-rewind.community.show', Controller\ShowCommunitySnapshotController::class)
        ->post('/rw-community/generate', 'huseyinfiliz-rewind.community.generate', Controller\GenerateCommunitySnapshotController::class)
        ->post('/rw-community/generate-steps', 'huseyinfiliz-rewind.community.generate-steps', Controller\GenerateCommunityStepsController::class)
        ->post('/rw-community/generate-step', 'huseyinfiliz-rewind.community.generate-step', Controller\GenerateCommunityStepController::class),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attribute('canViewRewind', fn (ForumSerializer $serializer) => $serializer->getActor()->hasPermission('huseyinfiliz-rewind.viewForum'))
        ->attribute('canGenerateRewind', fn (ForumSerializer $serializer) => $serializer->getActor()->hasPermission('huseyinfiliz-rewind.generate'))
        ->attribute('canModerateRewind', fn (ForumSerializer $serializer) => $serializer->getActor()->hasPermission('huseyinfiliz-rewind.moderate')),

    (new Extend\Conditional())
        ->whenExtensionEnabled('flarum-likes', fn () => [
            (new Extend\ServiceProvider())
                ->register(Metric\Optional\LikesMetricsProvider::class),
        ])
        ->whenExtensionEnabled('flarum-mentions', fn () => [
            (new Extend\ServiceProvider())
                ->register(Metric\Optional\MentionsMetricsProvider::class),
        ])
        ->whenExtensionEnabled('fof-badges', fn () => [
            (new Extend\ServiceProvider())
                ->register(Metric\Optional\BadgesMetricsProvider::class),
        ])
        ->whenExtensionEnabled('flarum-best-answer', fn () => [
            (new Extend\ServiceProvider())
                ->register(Metric\Optional\BestAnswersMetricsProvider::class),
        ]),

    (new Extend\Settings())
        ->serializeToForum('rewindEnabled', 'huseyinfiliz-rewind.enabled', 'boolval')
        ->serializeToForum('rewindYearRenderModes', 'huseyinfiliz-rewind.year_render_modes')
        ->serializeToForum('rewindActiveYear', 'huseyinfiliz-rewind.active_year', 'intval')
        ->serializeToForum('rewindShowMenu', 'huseyinfiliz-rewind.show_menu_link', 'boolval')
        ->serializeToForum('rewindHiddenUserSlides', 'huseyinfiliz-rewind.hidden_user_slides')
        ->serializeToForum('rewindHiddenCommunitySlides', 'huseyinfiliz-rewind.hidden_community_slides')
        ->default('huseyinfiliz-rewind.enabled', false)
        ->default('huseyinfiliz-rewind.year_render_modes', '{}')
        ->default('huseyinfiliz-rewind.active_year', 2025)
        ->default('huseyinfiliz-rewind.show_menu_link', true)
        ->default('huseyinfiliz-rewind.hidden_user_slides', '[]')
        ->default('huseyinfiliz-rewind.hidden_community_slides', '[]')
        ->default('huseyinfiliz-rewind.community_comparison_enabled', false),
];
