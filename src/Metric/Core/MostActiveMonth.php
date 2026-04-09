<?php

namespace HuseyinFiliz\Rewind\Metric\Core;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Flarum\Post\Post;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;

class MostActiveMonth implements RewindMetric
{
    public function key(): string
    {
        return 'most_active_month';
    }

    public function requiredExtension(): ?string
    {
        return null;
    }

    public function calculate(User $user, int $year): array
    {
        $dates = Post::where('user_id', $user->id)
            ->where('type', 'comment')
            ->whereYear('created_at', $year)
            ->pluck('created_at');

        $months = array_fill(1, 12, 0);
        $peakMonth = 0;
        $peakCount = 0;

        foreach ($dates as $date) {
            if ($date instanceof CarbonInterface) {
                $month = (int) $date->month;
            } elseif ($date instanceof DateTimeInterface) {
                $month = (int) $date->format('n');
            } else {
                $timestamp = strtotime((string) $date);
                if ($timestamp === false) {
                    continue;
                }
                $month = (int) date('n', $timestamp);
            }
            if ($month >= 1 && $month <= 12) {
                $months[$month]++;
            }
        }

        for ($i = 1; $i <= 12; $i++) {
            $count = $months[$i];
            if ($count > $peakCount) {
                $peakCount = $count;
                $peakMonth = $i;
            }
        }

        return [
            'months' => $months,
            'peak_month' => $peakMonth,
            'peak_count' => $peakCount,
        ];
    }
}
