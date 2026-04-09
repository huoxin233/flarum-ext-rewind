<?php

namespace HuseyinFiliz\Rewind\Search;

use Flarum\Search\Database\AbstractSearcher;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Illuminate\Database\Eloquent\Builder;

class RewindSnapshotSearcher extends AbstractSearcher
{
    public function getQuery(User $actor): Builder
    {
        $query = RewindSnapshot::query()->select('rw_snaps.*');

        $enabled = (bool) resolve(SettingsRepositoryInterface::class)->get('huseyinfiliz-rewind.enabled', false);

        if (! $actor->hasPermission('huseyinfiliz-rewind.moderate')) {
            if (! $enabled || ! $actor->hasPermission('huseyinfiliz-rewind.viewForum')) {
                $query->whereRaw('0 = 1');

                return $query;
            }
        }

        return $query;
    }
}
