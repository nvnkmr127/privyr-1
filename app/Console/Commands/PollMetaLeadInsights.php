<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollMetaLeadInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meta:poll-insights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Meta Graph API for Lead Ad campaign performance metrics and CPL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $adAccountId = config('services.facebook.ad_account_id');
        $accessToken = config('services.facebook.access_token');

        if (! $adAccountId || ! $accessToken) {
            $this->warn('Skipped: FACEBOOK_AD_ACCOUNT_ID or FACEBOOK_PAGE_ACCESS_TOKEN is missing in .env.');

            return 0;
        }

        $this->info("Polling Meta Graph API Insights for Ad Account: {$adAccountId}...");

        $response = Http::get("https://graph.facebook.com/v18.0/{$adAccountId}/insights", [
            'access_token' => $accessToken,
            'fields' => 'campaign_name,impressions,clicks,spend,actions',
            'date_preset' => 'last_30d',
        ]);

        if ($response->successful()) {
            $data = $response->json()['data'] ?? [];
            $this->info('Successfully fetched '.count($data).' campaign insight records.');
            Log::info('Meta Lead Insights polled successfully:', $data);

            return 0;
        }

        $this->error('Meta Graph API error: '.$response->body());

        return 1;
    }
}
