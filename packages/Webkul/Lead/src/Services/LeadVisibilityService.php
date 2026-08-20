<?php

namespace Webkul\Lead\Services;

use Webkul\Lead\Contracts\Lead;
use Webkul\User\Models\User;
use Webkul\User\Repositories\UserRepository;

class LeadVisibilityService
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get the list of User IDs whose leads the current user is permitted to see/access.
     * If null is returned, it means the user has 'global' access (no user_id filter needed).
     *
     * @param User $user
     * @param string $action Optional action parameter for future fine-grained scope overrides
     * @return array|null
     */
    public function getVisibleUserIds(User $user, string $action = 'view'): ?array
    {
        if ($user->view_permission == 'global') {
            return null;
        }

        if ($user->view_permission == 'group') {
            return $this->userRepository->getCurrentUserGroupsUserIds();
        }

        return [$user->id];
    }

    /**
     * Check if a specific user can access a specific lead.
     * 
     * @param User $user
     * @param Lead $lead
     * @param string $action Optional action
     * @return bool
     */
    public function canAccessLead(User $user, Lead $lead, string $action = 'view'): bool
    {
        $visibleUserIds = $this->getVisibleUserIds($user, $action);

        if ($visibleUserIds === null) {
            return true;
        }

        if (! $lead->user_id) {
             if ($lead->group_id && $user->view_permission == 'group') {
                 $userGroupIds = $user->groups()->pluck('id')->toArray();
                 if (in_array($lead->group_id, $userGroupIds)) {
                     return true;
                 }
             }
             return false;
        }

        return in_array($lead->user_id, $visibleUserIds);
    }
}
