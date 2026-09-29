<?php

/*
 * This file is part of huseyinfiliz/rewind.
 *
 * Copyright (c) 2026 Hüseyin Filiz.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace HuseyinFiliz\Rewind\Console;

use Carbon\Carbon;
use Flarum\Console\AbstractCommand;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\MetricRegistry;
use HuseyinFiliz\Rewind\Model\CommunitySnapshot;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenerateUserSnapshotsCommand extends AbstractCommand
{
    public function __construct(
        protected MetricRegistry $metricRegistry,
        protected SettingsRepositoryInterface $settings
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('rewind:generate-users')
            ->setDescription('Generate user rewind snapshots in batch for a given year')
            ->addArgument('year', InputArgument::OPTIONAL, 'The year to generate (defaults to active year in settings)')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Generate only for a specific user ID or username')
            ->addOption('group', null, InputOption::VALUE_REQUIRED, 'Generate only for users in a specific group ID')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Regenerate even if snapshot already exists');
    }

    protected function fire(): int
    {
        @set_time_limit(0);

        $activeYear = (int) $this->settings->get('huseyinfiliz-rewind.active_year', date('Y'));
        $yearArg = $this->input->getArgument('year');
        $year = $yearArg ? (int) $yearArg : $activeYear;

        if ($year < 2000 || $year > $activeYear) {
            $this->error("Invalid year {$year}. Year must be between 2000 and {$activeYear}.");

            return 1;
        }

        $singleUser = $this->input->getOption('user');
        $groupId = $this->input->getOption('group');
        $force = (bool) $this->input->getOption('force');

        $query = User::query();

        if ($singleUser) {
            if (is_numeric($singleUser)) {
                $query->where('id', (int) $singleUser);
            } else {
                $query->where('username', $singleUser);
            }
        } elseif ($groupId) {
            $query->whereHas('groups', function ($q) use ($groupId) {
                $q->where('id', (int) $groupId);
            });
        }

        if (! $force) {
            // Skip users who already have a snapshot for this year
            $existingUserIds = RewindSnapshot::where('year', $year)->pluck('user_id')->toArray();
            if (! empty($existingUserIds)) {
                $query->whereNotIn('id', $existingUserIds);
            }
        }

        $totalUsers = $query->count();

        if ($totalUsers === 0) {
            $this->info("No users to process for year {$year}.");

            return 0;
        }

        $this->info("Generating rewind snapshots for {$totalUsers} user(s) (Year: {$year})...");

        // Prepare community averages if enabled
        $communityAvg = null;
        if ($this->settings->get('huseyinfiliz-rewind.community_comparison_enabled')) {
            $communitySnapshot = CommunitySnapshot::where('year', $year)->first();
            if ($communitySnapshot && $communitySnapshot->data) {
                $cd = $communitySnapshot->data;
                $memberCount = max(1, $cd['new_users']['count'] ?? 1);
                $totalPosts = $cd['total_posts']['count'] ?? 0;
                $totalDiscussions = $cd['total_discussions']['count'] ?? 0;
                $totalWords = $cd['total_words']['total_words'] ?? 0;

                $communityAvg = [
                    'posts' => $memberCount > 0 ? round($totalPosts / $memberCount, 1) : 0,
                    'discussions' => $memberCount > 0 ? round($totalDiscussions / $memberCount, 1) : 0,
                    'words' => $memberCount > 0 ? round($totalWords / $memberCount) : 0,
                ];
            }
        }

        $progressBar = new ProgressBar($this->output, $totalUsers);
        $progressBar->start();

        $processed = 0;
        $query->chunk(100, function ($users) use ($year, $communityAvg, $progressBar, &$processed) {
            foreach ($users as $user) {
                $data = $this->metricRegistry->compute($user, $year);

                if ($communityAvg !== null) {
                    $data['_community_avg'] = $communityAvg;
                }

                RewindSnapshot::updateOrCreate(
                    ['user_id' => $user->id, 'year' => $year],
                    [
                        'data' => $data,
                        'generated_at' => Carbon::now(),
                    ]
                );

                $processed++;
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->output->writeln('');
        $this->info("Completed! Successfully generated {$processed} user rewind snapshot(s).");

        return 0;
    }
}
