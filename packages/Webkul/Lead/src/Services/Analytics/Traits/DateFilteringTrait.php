<?php

namespace Webkul\Lead\Services\Analytics\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DateFilteringTrait
{
    /**
     * Apply date range filtering.
     */
    protected function applyDateFiltering(Builder $query, string $startDate, string $endDate, string $dateColumn = 'leads.created_at'): Builder
    {
        return $query->whereBetween($dateColumn, [$startDate, $endDate]);
    }
}
