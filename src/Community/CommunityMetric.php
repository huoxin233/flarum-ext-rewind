<?php

namespace HuseyinFiliz\Rewind\Community;

interface CommunityMetric
{
    public function key(): string;

    public function requiredExtension(): ?string;

    public function calculate(int $year): array;
}
