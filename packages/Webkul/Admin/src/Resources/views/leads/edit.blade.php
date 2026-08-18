<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.leads.edit.title')
    </x-slot>

    {!! view_render_event('admin.leads.edit.form_controls.before', ['lead' => $lead]) !!}

    <!-- Edit Lead Form -->
    <x-admin::form         
        :action="route('admin.leads.update', $lead->id)"
        method="PUT"
    >
        <div class="max-w-7xl mx-auto flex flex-col gap-6 pt-4">
            <div class="scroll-reactive-sticky sticky top-[73px] z-[1000] flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white px-6 py-4 shadow-2xs">
                <div class="flex flex-col gap-1">
                    <x-admin::breadcrumbs 
                        name="leads.edit" 
                        :entity="$lead"
                    />

                    <div class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.leads.edit.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.leads.edit.save_button.before', ['lead' => $lead]) !!}

                    <!-- Save button for Editing Lead -->
                    <div class="flex items-center gap-x-2.5">
                        {!! view_render_event('admin.leads.edit.form_buttons.before') !!}

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.leads.edit.save-btn')
                        </button>

                        {!! view_render_event('admin.leads.edit.form_buttons.after') !!}
                    </div>

                    {!! view_render_event('admin.leads.edit.save_button.after', ['lead' => $lead]) !!}
                </div>
            </div>

            <input type="hidden" id="lead_pipeline_stage_id" name="lead_pipeline_stage_id" value="{{ $lead->lead_pipeline_stage_id }}" />

            <!-- Lead Edit Component -->
            <v-lead-edit :lead="{{ json_encode($lead) }}">
                <x-admin::shimmer.leads.datagrid />
            </v-lead-edit>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.leads.edit.form_controls.after', ['lead' => $lead]) !!}

    @pushOnce('scripts')
        <script 
            type="text/x-template"
            id="v-lead-edit-template"
        >
            <div class="flex flex-col gap-6 rounded-2xl border border-slate-200/80 bg-white shadow-2xs overflow-hidden">
                <div class="flex gap-2 border-b border-slate-100 px-6 pt-2">
                    <!-- Tabs -->
                    <template v-for="tab in tabs" :key="tab.id">
                        {!! view_render_event('admin.leads.edit.tabs.before', ['lead' => $lead]) !!}

                        <a
                            :href="'#' + tab.id"
                            :class="[
                                'inline-block px-4 py-3 border-b-2 text-[11px] font-bold tracking-widest uppercase transition',
                                activeTab === tab.id
                                ? 'text-slate-900 border-slate-900'
                                : 'text-slate-400 border-transparent hover:text-slate-900 hover:border-slate-300'
                            ]"
                            @click="scrollToSection(tab.id)"
                            :text="tab.label"
                        ></a>

                        {!! view_render_event('admin.leads.edit.tabs.after', ['lead' => $lead]) !!}
                    </template>
                </div>

                <div class="flex flex-col gap-8 px-8 py-6">
                    {!! view_render_event('admin.leads.edit.lead_details.before', ['lead' => $lead]) !!}

                    <!-- Details section -->
                    <div 
                        class="flex flex-col gap-4" 
                        id="lead-details"
                    >
                        <div class="flex flex-col gap-1 border-b border-slate-100 pb-4 mb-2">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.leads.edit.details')
                            </h2>

                            <p class="text-sm font-medium text-slate-500">
                                @lang('admin::app.leads.edit.details-info')
                            </p>
                        </div>

                        <div class="w-1/2 max-md:w-full">
                            {!! view_render_event('admin.leads.edit.lead_details.attributes.before', ['lead' => $lead]) !!}

                            <!-- Lead Attributes -->
                            <div class="grid grid-cols-2 gap-4">
                                <x-admin::attributes
                                    :custom-attributes="$attributes"
                                    :custom-validations="[
                                        'expected_close_date' => [
                                            'date_format:yyyy-MM-dd',
                                            'after:' .  \Carbon\Carbon::yesterday()->format('Y-m-d')
                                        ],
                                    ]"
                                    :entity="$lead"
                                />
                            </div>

                            <!-- Native Metadata Fields (Priority, Location, UTM, Qualification) -->
                            <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-gray-800">
                                <!-- Priority -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        Priority
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="priority"
                                        v-model="lead.priority"
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
                                        v-model="lead.location"
                                        placeholder="City, Country"
                                    />
                                </x-admin::form.control-group>

                                <!-- Qualification Status -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        Qualification Status
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="is_qualified"
                                        v-model="lead.is_qualified"
                                    >
                                        <option :value="0">Unqualified</option>
                                        <option :value="1">Qualified</option>
                                    </x-admin::form.control-group.control>
                                </x-admin::form.control-group>

                                <!-- UTM Source -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        UTM Source
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="utm_source"
                                        v-model="lead.utm_source"
                                    />
                                </x-admin::form.control-group>

                                <!-- UTM Medium -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        UTM Medium
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="utm_medium"
                                        v-model="lead.utm_medium"
                                    />
                                </x-admin::form.control-group>

                                <!-- UTM Campaign -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        UTM Campaign
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="utm_campaign"
                                        v-model="lead.utm_campaign"
                                    />
                                </x-admin::form.control-group>
                            </div>

                            {!! view_render_event('admin.leads.edit.lead_details.attributes.after', ['lead' => $lead]) !!}
                        </div>
                    </div>

                    {!! view_render_event('admin.leads.edit.lead_details.after', ['lead' => $lead]) !!}

                    {!! view_render_event('admin.leads.edit.contact_person.before', ['lead' => $lead]) !!}

                    <!-- Contact Person -->
                    <div 
                        class="flex flex-col gap-4" 
                        id="contact-person"
                    >
                        <div class="flex flex-col gap-1 border-b border-slate-100 pb-4 mb-2">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.leads.edit.contact-person')
                            </h2>

                            <p class="text-sm font-medium text-slate-500">
                                @lang('admin::app.leads.edit.contact-info')
                            </p>
                        </div>

                        <div class="w-1/2 max-md:w-full">
                            <div class="grid grid-cols-2 gap-4">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        Contact Name
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="person_name"
                                        rules="required"
                                        v-model="lead.person_name"
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
                                        v-model="lead.organization_name"
                                        placeholder="e.g. Acme Corp"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        Emails (Comma separated)
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="emails"
                                        :value="lead.emails ? lead.emails.join(', ') : ''"
                                        placeholder="john@example.com"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        Contact Numbers (Comma separated)
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="contact_numbers"
                                        :value="lead.contact_numbers ? lead.contact_numbers.join(', ') : ''"
                                        placeholder="+1 234 567 8900"
                                    />
                                </x-admin::form.control-group>
                            </div>
                        </div>
                    </div>

                    {!! view_render_event('admin.leads.edit.contact_person.after', ['lead' => $lead]) !!}

                    {!! view_render_event('admin.leads.edit.contact_person.products.before', ['lead' => $lead]) !!}

                    <!-- Product Section -->
                    <div 
                        class="flex flex-col gap-4" 
                        id="products"
                    >
                        <div class="flex flex-col gap-1 border-b border-slate-100 pb-4 mb-2">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.leads.edit.products')
                            </h2>

                            <p class="text-sm font-medium text-slate-500">
                                @lang('admin::app.leads.edit.products-info')
                            </p>
                        </div>

                        <div>
                            <!-- Product Component -->
                            @include('admin::leads.common.products')
                        </div>
                    </div>

                    {!! view_render_event('admin.leads.edit.contact_person.products.after', ['lead' => $lead]) !!}
                </div>
                
                {!! view_render_event('admin.leads.form_controls.after') !!}
            </div>
        </script>

        <script type="module">
            app.component('v-lead-edit', {
                template: '#v-lead-edit-template',

                data() {
                    return {
                        activeTab: 'lead-details',
                        
                        lead:  @json($lead),  
                        tabs: [
                            { id: 'lead-details', label: "@lang('admin::app.leads.edit.details')" },
                            { id: 'contact-person', label: "@lang('admin::app.leads.edit.contact-person')" },
                            { id: 'products', label: "@lang('admin::app.leads.edit.products')" }
                        ],
                    };
                },

                methods: {
                    /**
                     * Scroll to the section.
                     * 
                     * @param {String} tabId
                     * 
                     * @returns {void}
                     */
                    scrollToSection(tabId) {
                        const section = document.getElementById(tabId);

                        if (section) {
                            section.scrollIntoView({ behavior: 'smooth' });
                        }
                    },
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