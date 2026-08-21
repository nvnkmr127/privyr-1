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
            {{-- ponytail: workspace switcher removed — multi-tenancy is not implemented yet
                 (no workspace column, no switch_workspace route, no $workspaces var).
                 Re-add here when tenancy actually lands. --}}
            <a href="{{ route('admin.lead_capture.integrations.logs') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center gap-2 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 shadow-2xs">
                <i class="fa-solid fa-list-check"></i>
                <span>Capture Logs</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center gap-2 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 shadow-2xs">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- Integrations App -->
    <v-lead-capture-integrations
        :integrations='@json($integrations)'
        :pipelines='@json($pipelines)'
        :users='@json($users)'
        :workspace-id='@json(null)'
    ></v-lead-capture-integrations>
    
    @include('admin::lead_capture.wizard')
</x-admin::layouts>
