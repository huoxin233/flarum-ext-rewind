<?php

namespace HuseyinFiliz\Rewind\Metric\Optional;

use Flarum\Foundation\AbstractServiceProvider;
use HuseyinFiliz\Rewind\Metric\MetricRegistry;

class LikesMetricsProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->resolving(MetricRegistry::class, function (MetricRegistry $registry) {
            $registry->addMetric($this->container->make(LikesGiven::class));
            $registry->addMetric($this->container->make(LikesReceived::class));
            $registry->addMetric($this->container->make(BestFriend::class));
        });
    }
}
