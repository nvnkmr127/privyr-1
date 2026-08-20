<?php

namespace Webkul\Lead\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Services\LeadVisibilityService;
use Webkul\User\Models\User;

class LeadPolicy
{
    use HandlesAuthorization;

    /**
     * @var LeadVisibilityService
     */
    protected $visibilityService;

    public function __construct(LeadVisibilityService $visibilityService)
    {
        $this->visibilityService = $visibilityService;
    }

    /**
     * Determine whether the user can view the lead.
     */
    public function view(User $user, Lead $lead)
    {
        if (! bouncer()->hasPermission('leads.view')) {
            return false;
        }

        return $this->visibilityService->canAccessLead($user, $lead, 'view');
    }

    /**
     * Determine whether the user can create leads.
     */
    public function create(User $user)
    {
        return bouncer()->hasPermission('leads.create');
    }

    /**
     * Determine whether the user can update the lead.
     */
    public function update(User $user, Lead $lead)
    {
        if (! bouncer()->hasPermission('leads.edit')) {
            return false;
        }

        return $this->visibilityService->canAccessLead($user, $lead, 'edit');
    }

    /**
     * Determine whether the user can delete the lead.
     */
    public function delete(User $user, Lead $lead)
    {
        if (! bouncer()->hasPermission('leads.delete')) {
            return false;
        }

        return $this->visibilityService->canAccessLead($user, $lead, 'delete');
    }
}
