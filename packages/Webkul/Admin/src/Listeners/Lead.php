<?php

namespace Webkul\Admin\Listeners;

use Webkul\Email\Repositories\EmailRepository;

class Lead
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected EmailRepository $emailRepository) {}

    /**
     * @param  \Webkul\Lead\Models\Lead  $lead
     * @return void
     */
    public function linkToEmail($lead)
    {
        if (! request('email_id')) {
            return;
        }

        $this->emailRepository->update([
            'lead_id' => $lead->id,
        ], request('email_id'));
    }

    /**
     * @param  \Webkul\Lead\Models\Lead  $lead
     * @return void
     */
    public function handleStageActions($lead)
    {
        if (! $lead->wasRecentlyCreated && ! $lead->wasChanged('lead_pipeline_stage_id')) {
            return;
        }

        if (! $lead->stage || ! $lead->stage->actions) {
            return;
        }

        foreach ($lead->stage->actions as $action) {
            try {
                if ($action->type === 'assign_user') {
                    $user = \Webkul\User\Models\User::where('status', 1)->inRandomOrder()->first();
                    if ($user) {
                        $lead->updateQuietly(['user_id' => $user->id]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Stage action failed: ' . $e->getMessage());
            }
        }
    }
}
