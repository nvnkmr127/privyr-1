<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.create.title')
    </x-slot>

    {!! view_render_event('admin.leads.create.form.before') !!}

    <!-- Create Lead Form -->
    <x-admin::form :action="route('admin.leads.store')">
        <div class="max-w-3xl mx-auto space-y-8 pt-4 w-full">
            <!-- Top Page Header Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/60 pb-6">
                <div class="flex flex-col gap-1">
                    <x-admin::breadcrumbs name="leads.create" />
                    
                    <div class="flex items-center gap-3 mt-1">
                        <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                            @lang('admin::app.leads.create.title')
                        </h1>
                        <span class="rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700 shadow-sm">
                            New Lead
                        </span>
                    </div>
                    
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Add a new prospect to your CRM and start tracking activities.
                    </p>
                </div>

                {!! view_render_event('admin.leads.create.save_button.before') !!}

                <div class="flex items-center gap-x-3">
                    <a href="{{ route('admin.leads.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition">
                        Cancel
                    </a>
                    
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.leads.create.form_buttons.before') !!}

                        <button
                            type="submit"
                            class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-black shadow-md shadow-slate-900/10 transition flex items-center gap-2"
                        >
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @lang('admin::app.leads.create.save-btn')
                        </button>

                        {!! view_render_event('admin.leads.create.form_buttons.after') !!}
                    </div>
                </div>

                {!! view_render_event('admin.leads.create.save_button.after') !!}
            </div>

            @if (request('stage_id'))
                <input
                    type="hidden"
                    id="lead_pipeline_stage_id"
                    name="lead_pipeline_stage_id"
                    value="{{ request('stage_id') }}"
                />
            @endif

            @if (request('pipeline_id'))
                <input
                    type="hidden"
                    id="lead_pipeline_id"
                    name="lead_pipeline_id"
                    value="{{ request('pipeline_id') }}"
                />
            @endif

            <!-- Lead Create Component -->
            <v-lead-create>
                <x-admin::shimmer.leads.datagrid />
            </v-lead-create>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.leads.create.form.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-lead-create-template"
        >
            <div class="max-w-3xl mx-auto flex flex-col gap-6 items-start w-full">
                {!! view_render_event('admin.leads.edit.form_controls.before') !!}

                <!-- Auto-generated Title to bypass Krayin requirement -->
                <input type="hidden" name="title" value="New Lead" id="hidden_lead_title" />

                <!-- Contact Person -->
                <div class="w-full space-y-6">
                    {!! view_render_event('admin.leads.create.contact_person.before') !!}

                    <div class="flex flex-col gap-6 rounded-2xl border border-slate-200/80 bg-white shadow-2xs p-8" id="contact-person">
                        <div class="flex flex-col gap-1 border-b border-slate-100 pb-4 mb-2">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                                Contact Information
                            </h2>

                            <p class="text-sm font-medium text-slate-500">
                                Enter the prospect's primary contact details to create a new lead.
                            </p>
                        </div>

                        <div class="w-full">
                            <!-- Contact Person Component -->
                            @include('admin::leads.common.contact')
                        </div>
                    </div>

                    {!! view_render_event('admin.leads.create.contact_person.after') !!}
                </div>

                {!! view_render_event('admin.leads.form_controls.after') !!}
            </div>
        </script>

        <script type="module">
            app.component('v-lead-create', {
                template: '#v-lead-create-template',

                data() {
                    return {
                        // Layout is now grid-based, so tabs are no longer used for navigation,
                        // but we keep the logic empty to avoid errors if referenced elsewhere.
                    };
                },
            });
        </script>
    @endPushOnce

    @pushOnce('styles')
        <style>
            html {
                scroll-behavior: smooth;
            }
        </style>
    @endPushOnce
</x-admin::layouts>
