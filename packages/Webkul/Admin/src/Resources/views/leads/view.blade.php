<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.view.title', ['title' => strip_tags($lead->title)])
    </x-slot>

    <!-- Content -->
    <div class="max-w-7xl mx-auto space-y-4 pt-4">

        @if ($lead->duplicate_status === 'possible_duplicate' && $lead->duplicate_of_id)
            <div class="rounded-lg border border-slate-300 bg-slate-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start">
                    <div class="shrink-0 text-slate-900 dark:text-white mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    </div>
                    <div class="ml-3 flex-1 md:flex md:items-center md:justify-between">
                        <p class="text-sm text-slate-700 dark:text-gray-200">
                            <strong class="text-slate-900 dark:text-white">Possible Duplicate:</strong> This lead appears to be a duplicate of
                            <a href="{{ route('admin.leads.view', $lead->duplicate_of_id) }}" class="font-semibold underline hover:text-slate-900 dark:hover:text-white">Lead #{{ $lead->duplicate_of_id }}</a>.
                        </p>
                        <p class="mt-3 text-sm md:ml-6 md:mt-0">
                            <button type="button" class="whitespace-nowrap font-semibold text-slate-900 hover:text-slate-600 dark:text-white dark:hover:text-gray-300" onclick="document.getElementById('merge-modal').classList.remove('hidden')">
                                Compare & Merge <span aria-hidden="true">&rarr;</span>
                            </button>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Basic Merge Modal Placeholder -->
            <div id="merge-modal" data-lead-modal class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true" data-modal-backdrop></div>
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl dark:bg-gray-800">
                            <form method="POST" action="{{ route('admin.leads.merge.store', $lead->duplicate_of_id) }}">
                                @csrf
                                <input type="hidden" name="target_lead_id" value="{{ $lead->id }}">
                                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-gray-800">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100" id="modal-title">Merge Leads</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    You are about to merge Lead #{{ $lead->id }} into Lead #{{ $lead->duplicate_of_id }}.
                                                </p>
                                                <!-- Field selections can go here. For now it's simple merge. -->
                                                <div class="mt-4">
                                                    <label for="merge_reason" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Merge Reason (Optional)</label>
                                                    <div class="mt-2">
                                                        <input type="text" name="merge_reason" id="merge_reason" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-slate-900 sm:text-sm sm:leading-6 dark:bg-gray-900 dark:text-gray-100 dark:ring-gray-700 dark:focus:ring-white">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-slate-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 sm:ml-3 sm:w-auto dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">Confirm Merge</button>
                                    <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-700" onclick="document.getElementById('merge-modal').classList.add('hidden')">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Lead Summary Header -->
        {!! view_render_event('admin.leads.view.header.before', ['lead' => $lead]) !!}

        @include ('admin::leads.view.header')

        {!! view_render_event('admin.leads.view.header.after', ['lead' => $lead]) !!}

        <!-- Top Stages Navigation -->
        {!! view_render_event('admin.leads.view.stages.before', ['lead' => $lead]) !!}
        <div class="w-full">
            @include ('admin::leads.view.stages')
        </div>
        {!! view_render_event('admin.leads.view.stages.after', ['lead' => $lead]) !!}

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left Panel (Sidebar ~35% width, Sticky on Desktop) -->
            {!! view_render_event('admin.leads.view.left.before', ['lead' => $lead]) !!}

            <div class="lg:col-span-4 lg:sticky lg:top-4 self-start space-y-5">
                <!-- Follow Up Widget -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    @include ('admin::leads.view.follow-up')
                </div>
                
                <!-- Data Quality Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    @include ('admin::leads.view.data-quality')
                </div>

                <!-- Lead Health Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    @include ('admin::leads.view.health')
                </div>

                <!-- SLA Card -->
                @if($lead->slas->count() > 0)
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    @include ('admin::leads.view.sla')
                </div>
                @endif

                @if($lead->status === 'Nurturing' || $lead->nurtureHistories->count() > 0)
                <!-- Lead Nurturing Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    @include ('admin::leads.view.nurturing')
                </div>
                @endif

                <!-- Next Action / Lead Aging Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">Timeline Summary</h2>
                    <div class="flex flex-col gap-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Last Activity</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $lead->last_activity_at ? core()->formatDate($lead->last_activity_at, 'M d, Y h:i A') : '--' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Last Contact</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $lead->last_contacted_at ? core()->formatDate($lead->last_contacted_at, 'M d, Y h:i A') : '--' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Next Follow-up</span>
                            <span class="font-medium text-orange-600 dark:text-orange-400">
                                {{ $lead->next_follow_up_at ? core()->formatDate($lead->next_follow_up_at, 'M d, Y h:i A') : '--' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Qualification Framework Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    @include ('admin::leads.view.qualification')
                </div>

                <!-- Lead Attributes Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    @include ('admin::leads.view.attributes')
                </div>

                <!-- Lead Attribution Card -->
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900">
                    @include ('admin::leads.view.attribution')
                </div>
            </div>

            {!! view_render_event('admin.leads.view.left.after', ['lead' => $lead]) !!}

            <!-- Right Panel (Activity Timeline Workspace ~65% width) -->
            {!! view_render_event('admin.leads.view.right.before', ['lead' => $lead]) !!}

            <div class="lg:col-span-8 flex flex-col gap-5">
                <!-- Activities -->
                {!! view_render_event('admin.leads.view.activities.before', ['lead' => $lead]) !!}



                @php
                    $extraTypes = [
                        ['name' => 'timeline', 'label' => 'Timeline'],
                    ];
                    if (bouncer()->hasPermission('leads.view_audit')) {
                        $extraTypes[] = ['name' => 'audit', 'label' => 'Audit History'];
                    }
                @endphp
                <x-admin::activities
                    :endpoint="route('admin.leads.activities.index', $lead->id)"
                    :email-detach-endpoint="route('admin.leads.emails.detach', $lead->id)"
                    :activeType="request()->query('tab') ?? 'timeline'"
                    :extra-types="$extraTypes"
                >
                    <!-- Timeline Tab -->
                    <x-slot:timeline>
                        <div class="p-6 animate-[on-fade_0.5s_ease-in-out]">
                            @include('admin::leads.view.timeline')
                        </div>
                    </x-slot:timeline>

                    @if (bouncer()->hasPermission('leads.view_audit'))
                    <x-slot:audit>
                        <div class="p-6 animate-[on-fade_0.5s_ease-in-out]">
                            @include('admin::leads.view.audit')
                        </div>
                    </x-slot:audit>
                    @endif

                </x-admin::activities>

                {!! view_render_event('admin.leads.view.activities.after', ['lead' => $lead]) !!}
            </div>

            {!! view_render_event('admin.leads.view.right.after', ['lead' => $lead]) !!}
        </div>
    </div>

    @push('scripts')
        <script>
            // Accessibility for the hand-rolled lead modals: Escape to close,
            // backdrop click to dismiss, and move focus into the dialog on open.
            // (Focus-trap/tab-cycling is not implemented — migrate to
            // <x-admin::modal> if full trapping is needed.)
            (function () {
                const close = (el) => el && el.classList.add('hidden');

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        document.querySelectorAll('[data-lead-modal]:not(.hidden)').forEach(close);
                    }
                });

                document.querySelectorAll('[data-lead-modal]').forEach((modal) => {
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal || e.target.hasAttribute('data-modal-backdrop')) {
                            close(modal);
                        }
                    });

                    new MutationObserver(() => {
                        if (! modal.classList.contains('hidden')) {
                            modal.querySelector('input, button, textarea, select, a[href]')?.focus();
                        }
                    }).observe(modal, { attributes: true, attributeFilter: ['class'] });
                });
            })();
        </script>
    @endpush
</x-admin::layouts>
