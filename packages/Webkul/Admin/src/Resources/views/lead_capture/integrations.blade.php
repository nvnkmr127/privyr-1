<x-admin::layouts>
    <x-slot:title>
        Lead Capture Integrations
    </x-slot>

    <!-- Header -->
    <div class="flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 mb-4">
        <div class="flex flex-col gap-1">
            <x-admin::breadcrumbs name="settings" />
            <div class="text-xl font-bold dark:text-white">
                🔌 Lead Capture Integrations & Multi-Source Webhooks
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if (count($workspaces))
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Workspace</span>
                    <select id="lc-workspace-switcher" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-800 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        @foreach ($workspaces as $w)
                            <option value="{{ $w->id }}" @selected($w->id == $workspaceId)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <a href="{{ route('admin.settings.index') }}" class="secondary-button">
                ← Settings
            </a>
        </div>
    </div>

    @push('scripts')
        <script>
            // Tenant switcher: persist the selection server-side, then hard-reload
            // so all state (connectors, forms, tests) refetches for the new tenant.
            document.getElementById('lc-workspace-switcher')?.addEventListener('change', function (e) {
                var xsrf = decodeURIComponent((document.cookie.match(/XSRF-TOKEN=([^;]+)/) || [])[1] || '');
                fetch("{{ route('admin.lead_capture.integrations.switch_workspace') }}", {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'X-XSRF-TOKEN': xsrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ workspace_id: e.target.value }),
                }).then(function (r) {
                    if (r.ok) window.location.reload();
                    else alert('Could not switch workspace.');
                });
            });
        </script>
    @endpush

    <!-- Integrations App -->
    <v-lead-capture-integrations
        :integrations='@json($integrations)'
        :pipelines='@json($pipelines)'
        :users='@json($users)'
        :workspace-id='@json($workspaceId)'
    ></v-lead-capture-integrations>
    
    @include('admin::lead_capture.wizard')
</x-admin::layouts>
