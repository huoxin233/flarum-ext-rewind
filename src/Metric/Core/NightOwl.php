<?php

namespace HuseyinFiliz\Rewind\Metric\Core;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Flarum\Post\Post;
use Flarum\User\User;
use HuseyinFiliz\Rewind\Metric\RewindMetric;

class NightOwl implements RewindMetric
{
    public function key(): string
    {
        return 'night_owl';
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

        $hourCounts = array_fill(0, 24, 0);

        foreach ($dates as $date) {
            if ($date instanceof CarbonInterface) {
                $hour = (int) $date->hour;
            } elseif ($date instanceof DateTimeInterface) {
                $hour = (int) $date->format('G');
            } else {
                $timestamp = strtotime((string) $date);
                if ($timestamp === false) {
                    continue;
                }
                $hour = (int) date('G', $timestamp);
            }
            if ($hour >= 0 && $hour <= 23) {
                $hourCounts[$hour]++;
            }
        }

        $peakCount = max($hourCounts);

        if ($peakCount === 0) {
            return [
                'peak_hour' => null,
                'count' => 0,
                'is_night_owl' => false,
                'hour_counts' => $hourCounts,
            ];
        }

        $sorted = $hourCounts;
        arsort($sorted);
        $peakHour = array_key_first($sorted);

        // 22:00 (10 PM) ve 05:00 (5 AM) arası "Night Owl" kabul edelim
        $isNightOwl = ($peakHour >= 22 || $peakHour <= 5);

        return [
            'peak_hour' => $peakHour,
            'count' => $peakCount,
            'is_night_owl' => $isNightOwl,
            'hour_counts' => $hourCounts,
        ];
    }
}
