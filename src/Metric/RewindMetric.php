<?php

namespace HuseyinFiliz\Rewind\Metric;

use Flarum\User\User;

interface RewindMetric
{
    public function key(): string;

    public function requiredExtension(): ?string;

    public function calculate(User $user, int $year): array;
}
