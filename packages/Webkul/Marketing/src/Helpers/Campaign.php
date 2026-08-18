<?php

namespace Webkul\Marketing\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Marketing\Mail\CampaignMail;
use Webkul\Marketing\Repositories\CampaignRepository;
use Webkul\Marketing\Repositories\EventRepository;

class Campaign
{
    /**
     * Create a new helper instance.
     *
     *
     * @return void
     */
    public function __construct(
        protected EventRepository $eventRepository,
        protected CampaignRepository $campaignRepository,
        protected LeadRepository $leadRepository,
    ) {}

    /**
     * Process the email.
     */
    public function process(): void
    {
        $campaigns = $this->campaignRepository->getModel()
            ->leftJoin('marketing_events', 'marketing_campaigns.marketing_event_id', 'marketing_events.id')
            ->leftJoin('email_templates', 'marketing_campaigns.marketing_template_id', 'email_templates.id')
            ->select('marketing_campaigns.*')
            ->where('marketing_campaigns.status', 1)
            ->where(function ($query) {
                $query->where('marketing_events.date', Carbon::now()->format('Y-m-d'))
                    ->orWhereNull('marketing_events.date');
            })
            ->get();

        collect($campaigns)->each(function ($campaign) {
            collect($this->getLeadsEmails())->each(fn ($email) => Mail::queue(new CampaignMail($email, $campaign)));
        });
    }

    private function getLeadsEmails(): array
    {
        return $this->leadRepository->pluck('emails')
            ->flatMap(fn ($emails) => collect($emails)->pluck('value'))
            ->all();
    }
}
