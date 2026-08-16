<?php

namespace App\Support\Concerns;

use App\Support\WorkspaceContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tenant isolation for an Eloquent model that has a `workspace_id` column.
 *
 * - A global scope constrains every query to the current tenant (see
 *   WorkspaceContext). When there is no tenant context (console, queue, public
 *   webhooks) or the user is a super-admin, no constraint is added — so system
 *   flows and cross-workspace admins are unaffected.
 * - New records are auto-stamped with the current workspace on create.
 *
 * Because this drives `find()` too, cross-tenant record access (view / edit /
 * delete by id) returns "not found" for a non-owning tenant automatically.
 */
trait BelongsToWorkspace
{
    protected static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope('workspace', function (Builder $builder) {
            $workspaceId = app(WorkspaceContext::class)->currentWorkspaceId();

            if ($workspaceId !== null) {
                $builder->where($builder->getModel()->getTable().'.workspace_id', $workspaceId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->workspace_id)) {
                $workspaceId = app(WorkspaceContext::class)->currentWorkspaceId();

                if ($workspaceId !== null) {
                    $model->workspace_id = $workspaceId;
                }
            }
        });
    }

    /**
     * Escape hatch for legitimate cross-tenant queries (e.g. super-admin tooling).
     */
    public function scopeWithoutWorkspaceScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('workspace');
    }
}
