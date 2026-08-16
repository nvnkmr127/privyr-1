<x-admin::layouts>
    <x-slot:title>
        Lead Capture Integrations
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endpush

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 text-sm shadow-2xs dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 mb-4">
        <div class="flex flex-col gap-1 min-w-0">
            <x-admin::breadcrumbs name="settings" />
            <div class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-plug text-slate-600 dark:text-slate-400"></i>
                <span>Lead Capture Integrations &amp; Multi-Source Webhooks</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if (count($workspaces))
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Workspace</span>
                    <select id="lc-workspace-switcher" class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-1.5 text-xs font-bold text-slate-900 shadow-2xs dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        @foreach ($workspaces as $w)
                            <option value="{{ $w->id }}" @selected($w->id == $workspaceId)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <a href="{{ route('admin.settings.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center gap-2 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 shadow-2xs">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Settings</span>
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
