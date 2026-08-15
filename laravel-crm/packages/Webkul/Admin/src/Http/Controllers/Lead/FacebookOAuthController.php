<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Models\LeadSourceConnector;

class FacebookOAuthController extends Controller
{
    private const GRAPH = 'https://graph.facebook.com/v18.0';

    private const SCOPES = 'pages_show_list,pages_read_engagement,pages_manage_metadata,leads_retrieval,business_management';

    /**
     * Redirect the admin to Facebook's OAuth dialog to authorize a Page.
     */
    public function connect(int $id)
    {
        $connector = LeadSourceConnector::findOrFail($id);

        $clientId = config('services.facebook.client_id');

        if (! $clientId || ! config('services.facebook.client_secret')) {
            session()->flash('error', 'Set FACEBOOK_CLIENT_ID and FACEBOOK_CLIENT_SECRET before connecting a Facebook Page.');

            return redirect()->route('admin.settings.lead_connectors.index');
        }

        $state = Str::random(40);
        session(['fb_oauth_state' => $state, 'fb_oauth_connector_id' => $connector->id]);

        $query = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => route('admin.settings.lead_connectors.facebook.callback'),
            'state'         => $state,
            'scope'         => self::SCOPES,
            'response_type' => 'code',
        ]);

        return redirect()->away("https://www.facebook.com/v18.0/dialog/oauth?{$query}");
    }

    /**
     * OAuth callback: exchange the code for a long-lived Page token, store it on
     * the connector, and subscribe the Page to the leadgen webhook field.
     */
    public function callback(Request $request)
    {
        $redirect = redirect()->route('admin.settings.lead_connectors.index');

        // CSRF protection for the OAuth round-trip.
        if (! $request->filled('state') || $request->get('state') !== session('fb_oauth_state')) {
            session()->flash('error', 'Facebook connection failed: invalid state. Please try again.');

            return $redirect;
        }

        $connector = LeadSourceConnector::find(session('fb_oauth_connector_id'));
        session()->forget(['fb_oauth_state', 'fb_oauth_connector_id']);

        if (! $connector) {
            session()->flash('error', 'Facebook connection failed: connector no longer exists.');

            return $redirect;
        }

        if ($request->filled('error')) {
            session()->flash('error', 'Facebook authorization was cancelled or denied.');

            return $redirect;
        }

        try {
            // 1. Exchange the code for a short-lived user token.
            $short = Http::get(self::GRAPH.'/oauth/access_token', [
                'client_id'     => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'redirect_uri'  => route('admin.settings.lead_connectors.facebook.callback'),
                'code'          => $request->get('code'),
            ])->throw()->json('access_token');

            // 2. Upgrade to a long-lived user token.
            $long = Http::get(self::GRAPH.'/oauth/access_token', [
                'grant_type'        => 'fb_exchange_token',
                'client_id'         => config('services.facebook.client_id'),
                'client_secret'     => config('services.facebook.client_secret'),
                'fb_exchange_token' => $short,
            ])->throw()->json('access_token');

            // 3. List the Pages this user manages (Page tokens are long-lived here).
            $pages = Http::get(self::GRAPH.'/me/accounts', ['access_token' => $long])->throw()->json('data', []);

            if (empty($pages)) {
                session()->flash('error', 'No Facebook Pages found for this account.');

                return $redirect;
            }

            // Pick the requested Page, else the first one.
            $page = collect($pages)->firstWhere('id', $request->get('page_id')) ?? $pages[0];

            // 4. Subscribe the Page to the leadgen webhook field.
            Http::asForm()->post(self::GRAPH."/{$page['id']}/subscribed_apps", [
                'subscribed_fields' => 'leadgen',
                'access_token'      => $page['access_token'],
            ])->throw();

            // 5. Persist the Page token on the connector.
            $connector->update([
                'meta_page_id'           => $page['id'],
                'meta_page_name'         => $page['name'] ?? null,
                'meta_page_access_token' => $page['access_token'],
                'is_active'              => true,
            ]);

            $extra = count($pages) > 1 ? ' ('.(count($pages) - 1).' other Page(s) available — reconnect to switch.)' : '';
            session()->flash('success', 'Connected Facebook Page: '.($page['name'] ?? $page['id']).'.'.$extra);
        } catch (\Throwable $e) {
            Log::error('Facebook OAuth callback failed: '.$e->getMessage());
            session()->flash('error', 'Facebook connection failed: '.$e->getMessage());
        }

        return $redirect;
    }
}
