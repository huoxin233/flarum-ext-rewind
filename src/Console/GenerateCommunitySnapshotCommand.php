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
use HuseyinFiliz\Rewind\Community\CommunityMetricRegistry;
use HuseyinFiliz\Rewind\Model\CommunitySnapshot;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenerateCommunitySnapshotCommand extends AbstractCommand
{
    public function __construct(
        protected CommunityMetricRegistry $metricRegistry,
        protected SettingsRepositoryInterface $settings
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('rewind:generate-community')
            ->setDescription('Generate community rewind snapshot for a given year')
            ->addArgument('year', InputArgument::OPTIONAL, 'The year to generate (defaults to active year in settings)')
            ->addOption('step', null, InputOption::VALUE_REQUIRED, 'Run only a specific metric step')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Bypass rate limit check');
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

        $singleStep = $this->input->getOption('step');
        $force = (bool) $this->input->getOption('force');

        $this->info("Generating community rewind snapshot for year {$year}...");

        $existing = CommunitySnapshot::where('year', $year)->first();
        if (! $force && $existing && $existing->generated_at && $existing->generated_at->diffInSeconds(Carbon::now()) < 60) {
            $this->warn('Snapshot was generated less than 1 minute ago. Use --force to regenerate.');

            return 0;
        }

        if ($singleStep) {
            $this->info("Computing single metric: {$singleStep}...");
            $result = $this->metricRegistry->computeOne($singleStep, $year);

            if ($result === null) {
                $this->error("Metric '{$singleStep}' returned no data or is not available.");

                return 1;
            }

            $snapshot = CommunitySnapshot::firstOrCreate(
                ['year' => $year],
                ['data' => [], 'generated_at' => Carbon::now()]
            );

            $data = $snapshot->data ?? [];
            $data[$singleStep] = $result;
            $snapshot->data = $data;
            $snapshot->generated_at = Carbon::now();
            $snapshot->save();

            $this->info("Successfully updated metric '{$singleStep}' for year {$year}.");

            return 0;
        }

        // Calculate all metrics step-by-step with progress feedback in CLI
        $keys = $this->metricRegistry->availableKeys();
        $total = count($keys);
        $this->info("Computing {$total} community metrics...");

        $snapshot = CommunitySnapshot::firstOrCreate(
            ['year' => $year],
            ['data' => [], 'generated_at' => Carbon::now()]
        );

        $data = $snapshot->data ?? [];

        foreach ($keys as $index => $key) {
            $stepNum = $index + 1;
            $this->output->write("  [{$stepNum}/{$total}] {$key}... ");

            $start = microtime(true);
            $result = $this->metricRegistry->computeOne($key, $year);
            $elapsed = round(microtime(true) - $start, 2);

            if ($result !== null) {
                $data[$key] = $result;
                $this->output->writeln("<info>Done</info> ({$elapsed}s)");
            } else {
                $this->output->writeln('<comment>Skipped / null</comment>');
            }
        }

        $snapshot->data = $data;
        $snapshot->generated_at = Carbon::now();
        $snapshot->save();

        $this->info("Community rewind snapshot for year {$year} successfully generated!");

        return 0;
    }
}
