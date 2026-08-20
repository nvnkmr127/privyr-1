<?php

namespace Webkul\Lead\Services\Analytics\Traits;

use Illuminate\Database\Eloquent\Builder;

trait AclFilteringTrait
{
    /**
     * Apply ACL scoping to ensure users only see aggregate metrics for leads they can access.
     */
    protected function applyAclFiltering(Builder $query, string $userIdColumn = 'leads.user_id'): Builder
    {
        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $query->whereIn($userIdColumn, $userIds);
        }

        return $query;
    }
}
