@props([
    'entity' => null,
    'entityControlName' => null,
])

<!-- Activity Button -->
<div>
    {!! view_render_event('admin.components.activities.actions.activity.create_btn.before') !!}

    <button
        class="flex h-20 w-full flex-col items-center justify-center gap-1.5 rounded-xl border border-transparent bg-purple-50 text-xs font-bold text-purple-600 transition-all hover:bg-purple-100"
        @click="$refs.actionComponent.openModal('mail')"
    >
        <span class="icon-activity text-2xl"></span>

        @lang('admin::app.components.activities.actions.activity.btn')
    </button>

    {!! view_render_event('admin.components.activities.actions.activity.create_btn.after') !!}

    {!! view_render_event('admin.components.activities.actions.activity.before') !!}

    <!-- Note Action Vue Component -->
    <v-activity
        ref="actionComponent"
        :entity="{{ json_encode($entity) }}"
        entity-control-name="{{ $entityControlName }}"
    ></v-activity>

    {!! view_render_event('admin.components.activities.actions.activity.after') !!}
</div>


@pushOnce('scripts')
    <script type="text/x-template" id="v-activity-template">
        <Teleport to="body">
            {!! view_render_event('admin.components.activities.actions.activity.form_controls.before') !!}

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit, resetForm }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, save)">
                    {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.before') !!}

                    <x-admin::drawer
                        ref="activityModal"
                        width="500px"
                    >
                        <x-slot:header>
                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.header.dropdown.before') !!}

                            <div class="flex items-center gap-3.5">
                                <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br shadow-sm ring-1 dark:ring-opacity-50', selectedType.colorClass]">
                                    <span :class="[selectedType.icon, 'text-2xl']"></span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                                        @lang('admin::app.components.activities.actions.activity.title')
                                    </h3>
                                    
                                    <x-admin::dropdown>
                                        <x-slot:toggle>
                                            <div class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50/80 px-3 py-1.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-100 hover:shadow dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-200 dark:hover:bg-gray-800">
                                                @{{ selectedType.label }}
                                                <span class="icon-down-arrow text-lg opacity-70"></span>
                                            </div>
                                        </x-slot>

                                    <x-slot:menu>
                                        {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.header.dropdown.menu_item.before') !!}

                                        <x-admin::dropdown.menu.item
                                            ::class="{ 'bg-gray-100 dark:bg-gray-950': selectedType.value === type.value }"
                                            v-for="type in availableTypes"
                                            @click="selectedType = type"
                                        >
                                            @{{ type.label }}
                                        </x-admin::dropdown.menu.item>

                                        {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.header.dropdown.menu_item.after') !!}
                                    </x-slot>
                                    </x-admin::dropdown>
                                </div>
                            </div>

                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.header.dropdown.after') !!}
                        </x-slot>

                        <x-slot:content>
                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.content.controls.before') !!}

                            <!-- Activity Type -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="type"
                                v-model="selectedType.value"
                            />

                            <!-- Id -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                ::name="entityControlName"
                                ::value="entity.id"
                            />

                            <!-- Title -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required font-semibold text-gray-800 dark:text-gray-200">
                                    @lang('admin::app.components.activities.actions.activity.title-control')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="title"
                                    rules="required"
                                    class="w-full rounded border border-gray-300 bg-white px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                    :label="trans('admin::app.components.activities.actions.activity.title-control')"
                                    placeholder="Enter activity title"
                                />

                                <x-admin::form.control-group.error control-name="title" />
                            </x-admin::form.control-group>

                            <!-- Description -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="font-semibold text-gray-800 dark:text-gray-200">
                                    @lang('admin::app.components.activities.actions.activity.description')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="comment"
                                    class="!h-[120px] w-full resize-y rounded border border-gray-300 bg-white px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                    :label="trans('admin::app.components.activities.actions.activity.description')"
                                    placeholder="Add description..."
                                />

                                <x-admin::form.control-group.error control-name="comment" />
                            </x-admin::form.control-group>

                            <!-- Participants -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="font-semibold text-gray-800 dark:text-gray-200">
                                    @lang('admin::app.components.activities.actions.activity.participants.title')
                                </x-admin::form.control-group.label>

                                <x-admin::activities.actions.activity.participants />
                                
                                <x-admin::form.control-group.error control-name="participants" />
                            </x-admin::form.control-group>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Started From -->
                                <x-admin::form.control-group class="w-full">
                                    <x-admin::form.control-group.label class="required font-semibold text-gray-800 dark:text-gray-200">
                                        @lang('admin::app.components.activities.actions.activity.schedule-from')
                                    </x-admin::form.control-group.label>
                                    
                                    <x-admin::form.control-group.control
                                        type="datetime"
                                        name="schedule_from"
                                        rules="required"
                                        class="w-full rounded border border-gray-300 bg-white px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        :label="trans('admin::app.components.activities.actions.activity.schedule-from')"
                                    />

                                    <x-admin::form.control-group.error control-name="schedule_from" />
                                </x-admin::form.control-group>
                                
                                <!-- Started To -->
                                <x-admin::form.control-group class="w-full">
                                    <x-admin::form.control-group.label class="required font-semibold text-gray-800 dark:text-gray-200">
                                        @lang('admin::app.components.activities.actions.activity.schedule-to')
                                    </x-admin::form.control-group.label>
                                    
                                    <x-admin::form.control-group.control
                                        type="datetime"
                                        name="schedule_to"
                                        rules="required|after:schedule_from"
                                        class="w-full rounded border border-gray-300 bg-white px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        :label="trans('admin::app.components.activities.actions.activity.schedule-to')"
                                    />

                                    <x-admin::form.control-group.error control-name="schedule_to" />
                                </x-admin::form.control-group>
                            </div>

                            <!-- Location -->
                            <x-admin::form.control-group v-if="selectedType.hasLocation" class="!mb-0">
                                <x-admin::form.control-group.label class="font-semibold text-gray-800 dark:text-gray-200">
                                    @lang('admin::app.components.activities.actions.activity.location')
                                </x-admin::form.control-group.label>
                                
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="location"
                                    class="w-full rounded border border-gray-300 bg-white px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                    placeholder="Add a location..."
                                />
                            </x-admin::form.control-group>

                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.content.controls.after') !!}
                        </x-slot>

                        <x-slot:footer>
                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.footer.save_button.before') !!}

                            <div class="flex items-center gap-x-4">
                                <button
                                    type="button"
                                    class="transparent-button px-4 py-2"
                                    @click="resetForm(); $refs.activityModal.close()"
                                >
                                    Cancel
                                </button>
                                
                                <x-admin::button
                                    button-class="primary-button"
                                    :title="trans('admin::app.components.activities.actions.activity.save-btn')"
                                    :loading-title="trans('admin::app.components.activities.actions.activity.saving')"
                                    ::loading="isStoring"
                                    ::disabled="isStoring || !meta.valid"
                                />
                            </div>

                            {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.footer.save_button.after') !!}
                        </x-slot>
                    </x-admin::drawer>

                    {!! view_render_event('admin.components.activities.actions.activity.form_controls.modal.after') !!}
                </form>
            </x-admin::form>

            {!! view_render_event('admin.components.activities.actions.activity.form_controls.after') !!}
        </Teleport>
    </script>

    <script type="module">
        app.component('v-activity', {
            template: '#v-activity-template',

            props: {
                entity: {
                    type: Object,
                    required: true,
                    default: () => {}
                },

                entityControlName: {
                    type: String,
                    required: true,
                    default: ''
                }
            },

            data: function () {
                return {
                    isStoring: false,
                    
                    selectedType: {
                        label: "{{ trans('admin::app.components.activities.actions.activity.call') }}",
                        value: 'call'
                    },

                    availableTypes: [
                        {
                            label: "{{ trans('admin::app.components.activities.actions.activity.call') }}",
                            value: 'call',
                            icon: 'icon-call',
                            colorClass: 'from-blue-50 to-indigo-100 ring-blue-200/50 text-blue-600 dark:from-blue-900/50 dark:to-indigo-950/50 dark:text-blue-400 dark:ring-blue-700',
                            hasLocation: false
                        }, {
                            label: "{{ trans('admin::app.components.activities.actions.activity.meeting') }}",
                            value: 'meeting',
                            icon: 'icon-meeting',
                            colorClass: 'from-purple-50 to-fuchsia-100 ring-purple-200/50 text-purple-600 dark:from-purple-900/50 dark:to-fuchsia-950/50 dark:text-purple-400 dark:ring-purple-700',
                            hasLocation: true
                        }, {
                            label: "{{ trans('admin::app.components.activities.actions.activity.lunch') }}",
                            value: 'lunch',
                            icon: 'icon-activity',
                            colorClass: 'from-emerald-50 to-teal-100 ring-emerald-200/50 text-emerald-600 dark:from-emerald-900/50 dark:to-teal-950/50 dark:text-emerald-400 dark:ring-emerald-700',
                            hasLocation: true
                        },
                    ]
                }
            },

            watch: {
                selectedType: function (newType) {
                    if (!newType.hasLocation) {
                        // Reset location field safely when switching to a type that doesn't need it
                        const locationInput = this.$refs.modalForm.$el.querySelector('input[name="location"]');
                        if (locationInput) {
                            locationInput.value = '';
                            locationInput.dispatchEvent(new Event('input'));
                        }
                    }
                }
            },

            methods: {
                openModal(type) {
                    this.$refs.activityModal.open();
                },

                save(params, { resetForm, setErrors }) {
                    this.isStoring = true;

                    this.$axios.post("{{ route('admin.activities.store') }}", params)
                        .then (response => {
                            this.isStoring = false;

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$emitter.emit('on-activity-added', response.data.data);

                            resetForm();

                            this.$refs.activityModal.close();
                        })
                        .catch (error => {
                            this.isStoring = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            } else {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce