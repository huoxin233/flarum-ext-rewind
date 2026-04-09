<?php

namespace HuseyinFiliz\Rewind\Search\Filter;

use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;

/**
 * @implements FilterInterface<SearchState>
 */
class YearFilter implements FilterInterface
{
    public function getFilterKey(): string
    {
        return 'year';
    }

    public function filter(SearchState $state, string|array $value, bool $negate): void
    {
        $year = is_array($value) ? $value[0] : $value;

        $state->getQuery()->where('year', $negate ? '!=' : '=', (int) $year);
    }
}
