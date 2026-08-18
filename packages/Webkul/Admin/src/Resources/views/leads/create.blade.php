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
                    <a href="{{ route('admin.leads.index') }}" class="transparent-button">
                        Cancel
                    </a>
                    
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.leads.create.form_buttons.before') !!}

                        <button
                            type="submit"
                            class="primary-button"
                        >
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
                            <div class="grid grid-cols-2 gap-4">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        Contact Name
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="person_name"
                                        rules="required"
                                        placeholder="e.g. John Doe"
                                    />
                                    <x-admin::form.control-group.error control-name="person_name" />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        Organization Name
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="organization_name"
                                        placeholder="e.g. Acme Corp"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        Emails (Comma separated)
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="emails[]"
                                        placeholder="john@example.com"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        Contact Numbers (Comma separated)
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="contact_numbers[]"
                                        placeholder="+1 234 567 8900"
                                    />
                                </x-admin::form.control-group>
                            </div>
                        </div>
                    </div>

                    {!! view_render_event('admin.leads.create.contact_person.after') !!}
                </div>

                <!-- Lead Details -->
                <div class="w-full space-y-6 mt-6">
                    <div class="flex flex-col gap-6 rounded-2xl border border-slate-200/80 bg-white shadow-2xs p-8" id="lead-details">
                        <div class="flex flex-col gap-1 border-b border-slate-100 pb-4 mb-2">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                                Lead Details
                            </h2>
                            <p class="text-sm font-medium text-slate-500">
                                Enter the lead value, pipeline, priority, source, and other parameters.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 w-full">
                            <!-- Lead Title -->
                            <x-admin::form.control-group class="col-span-2">
                                <x-admin::form.control-group.label class="required">
                                    Lead Title
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="title"
                                    rules="required"
                                    value="New Lead"
                                />
                                <x-admin::form.control-group.error control-name="title" />
                            </x-admin::form.control-group>

                            <!-- Lead Value -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Lead Value ($)
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="lead_value"
                                    placeholder="e.g. 500"
                                />
                            </x-admin::form.control-group>

                            <!-- Priority -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Priority
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="priority"
                                    value="medium"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Location -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Location
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="location"
                                    placeholder="City, Country"
                                />
                            </x-admin::form.control-group>

                            <!-- Source -->
                            @php
                                $sources = app(\Webkul\Lead\Repositories\SourceRepository::class)->all();
                            @endphp
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Source
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="lead_source_id"
                                    rules="required"
                                >
                                    @foreach ($sources as $source)
                                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                                    @endforeach
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Type -->
                            @php
                                $types = app(\Webkul\Lead\Repositories\TypeRepository::class)->all();
                            @endphp
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Type
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="lead_type_id"
                                    rules="required"
                                >
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Description -->
                            <x-admin::form.control-group class="col-span-2">
                                <x-admin::form.control-group.label>
                                    Description
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="description"
                                    placeholder="Enter lead details or notes..."
                                />
                            </x-admin::form.control-group>
                        </div>

                        <!-- Custom Attributes -->
                        @if ($attributes->where('is_user_defined', 1)->count() > 0)
                            <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-slate-100">
                                <x-admin::attributes
                                    :custom-attributes="$attributes->where('is_user_defined', 1)"
                                />
                            </div>
                        @endif
                    </div>
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
